<?php

namespace App\Livewire\Operators;

use App\Enums\CancellationPolicy;
use App\Enums\PackageStatus;
use App\Enums\PackageType;
use App\Models\Category;
use App\Models\Destination;
use App\Models\Package;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.app')]
class PackageForm extends Component
{
    use WithFileUploads;

    public const STEPS = ['Basics', 'Details', 'Pricing', 'Photos'];
    public const INCLUSIONS = ['Meals', 'Accommodation', 'Transport', 'Park fees', 'Guide', 'Equipment', 'Drinks'];
    public const DAYS = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
    public const FEE_PERCENT = 8;

    #[Locked]
    public ?int $packageId = null;

    public int $step = 0;

    public string $type = 'trip';
    public string $title = '';
    public ?int $destination_id = null;
    public ?int $category_id = null;
    public string $summary = '';
    public string $description = '';

    public array $plan = [''];
    public ?float $duration_hours = 3;
    public string $start_time = '08:00';
    public string $meeting_point = '';
    public string $bring = '';
    public array $included = [];
    public string $excluded = '';

    public ?int $price = null;
    public int $child_discount_percent = 0;
    public ?int $max_group_size = null;
    public string $cancellation_policy = 'flexible';
    public array $departures = [''];
    public array $weekdays = ['Sat', 'Sun'];

    public array $photos = [];
    public array $existing = [];
    public array $removed = [];
    public string $video_url = '';

    public function mount(?Package $package = null): void
    {
        abort_unless(auth()->user()->isOperator(), 403);

        if (! $package?->exists) {
            return;
        }

        abort_unless($package->user_id === auth()->id(), 403);

        $this->packageId = $package->id;
        $this->type = $package->type->value;
        $this->title = $package->title;
        $this->destination_id = $package->destination_id;
        $this->category_id = $package->category_id;
        $this->summary = (string) $package->summary;
        $this->description = (string) $package->description;
        $this->plan = $package->days->pluck('description')->all() ?: [''];
        $this->duration_hours = $package->duration_hours ?? 3;
        $this->start_time = $package->start_time ? substr($package->start_time, 0, 5) : '08:00';
        $this->meeting_point = (string) $package->meeting_point;
        $this->bring = (string) $package->bring;
        $this->included = $package->included->pluck('label')->all();
        $this->excluded = $package->excluded->pluck('label')->implode("\n");
        $this->price = $package->price;
        $this->child_discount_percent = $package->child_discount_percent;
        $this->max_group_size = $package->max_group_size;
        $this->cancellation_policy = $package->cancellation_policy->value;
        $this->departures = $package->departures->map(fn($d) => $d->starts_on->format('Y-m-d'))->all() ?: [''];
        $this->weekdays = $package->weekdays ?? [];
        $this->existing = $package->getMedia('photos')
            ->map(fn($m) => ['id' => $m->id, 'url' => $m->getUrl('card')])->all();
        $this->video_url = (string) $package->video_url;
    }

    public function toggle(string $field, string $value): void
    {
        abort_unless(in_array($field, ['included', 'weekdays'], true), 422);

        $this->{$field} = in_array($value, $this->{$field}, true)
            ? array_values(array_diff($this->{$field}, [$value]))
            : [...$this->{$field}, $value];
    }

    public function addDay(): void
    {
        $this->plan[] = '';
    }

    public function removeDay(int $i): void
    {
        unset($this->plan[$i]);
        $this->plan = array_values($this->plan) ?: [''];
    }

    public function addDeparture(): void
    {
        $this->departures[] = '';
    }

    public function removeDeparture(int $i): void
    {
        unset($this->departures[$i]);
        $this->departures = array_values($this->departures) ?: [''];
    }

    public function removePhoto(int $i): void
    {
        array_splice($this->photos, $i, 1);
    }

    public function removeExisting(int $mediaId): void
    {
        if (collect($this->existing)->contains('id', $mediaId)) {
            $this->removed[] = $mediaId;
            $this->existing = array_values(array_filter($this->existing, fn($p) => $p['id'] !== $mediaId));
        }
    }

    public function next(): void
    {
        $this->validate($this->rulesFor($this->step));
        $this->step++;
    }

    public function back(): void
    {
        $this->step = max(0, $this->step - 1);
    }

    public function save(string $mode): void
    {
        abort_unless(in_array($mode, ['draft', 'publish'], true), 422);

        if ($mode === 'draft') {
            $this->validate([
                'type' => ['required', Rule::enum(PackageType::class)],
                'title' => 'required|string|min:5|max:120',
                'destination_id' => 'required|exists:destinations,id',
                'category_id' => 'required|exists:categories,id',
            ]);
        } else {
            foreach (array_keys(self::STEPS) as $s) {
                $this->step = $s;
                $this->validate($this->rulesFor($s));
            }

            if (count($this->existing) + count($this->photos) < 1) {
                $this->step = 3;
                $this->addError('photos', 'Add at least one photo.');

                return;
            }
        }

        $this->persist($mode);

        session()->flash('status', $mode === 'publish'
            ? 'Package saved. It goes live once your account is verified.'
            : 'Draft saved.');

        $this->redirectRoute('operators.show', ['profile' => auth()->user()->operatorProfile->slug], navigate: true);
    }

    private function persist(string $mode): void
    {
        DB::transaction(function () use ($mode) {
            $trip = $this->type === 'trip';
            $plan = array_values(array_filter(array_map('trim', $this->plan)));

            $package = $this->packageId
                ? Package::where('user_id', auth()->id())->findOrFail($this->packageId)
                : new Package(['user_id' => auth()->id()]);

            $package->fill([
                'type' => $this->type,
                'title' => $this->title,
                'destination_id' => $this->destination_id,
                'category_id' => $this->category_id,
                'summary' => $this->summary ?: null,
                'description' => $this->description ?: null,
                'duration_days' => $trip ? (count($plan) ?: null) : null,
                'duration_hours' => $trip ? null : $this->duration_hours,
                'start_time' => $trip ? null : $this->start_time,
                'meeting_point' => $trip ? null : ($this->meeting_point ?: null),
                'bring' => $trip ? null : ($this->bring ?: null),
                'weekdays' => $trip ? null : array_values($this->weekdays),
                'price' => $this->price ?? 0,
                'child_discount_percent' => $this->child_discount_percent,
                'max_group_size' => $this->max_group_size,
                'cancellation_policy' => $this->cancellation_policy,
                'video_url' => $this->video_url ?: null,
                'status' => $mode === 'publish' ? PackageStatus::Published : PackageStatus::Draft,
            ]);

            if ($mode === 'publish') {
                $package->published_at ??= now();
            }

            $package->save();

            $package->days()->delete();
            if ($trip) {
                foreach ($plan as $i => $text) {
                    $package->days()->create(['day_number' => $i + 1, 'description' => $text]);
                }
            }

            $package->inclusions()->delete();
            foreach (array_values($this->included) as $i => $label) {
                $package->inclusions()->create(['label' => $label, 'is_included' => true, 'sort_order' => $i]);
            }
            foreach (array_values(array_filter(array_map('trim', explode("\n", $this->excluded)))) as $i => $label) {
                $package->inclusions()->create(['label' => $label, 'is_included' => false, 'sort_order' => 100 + $i]);
            }

            if ($trip) {
                $dates = collect($this->departures)->filter()->unique()->values();
                $package->departures()->whereNotIn('starts_on', $dates)->where('seats_booked', 0)->delete();
                foreach ($dates as $date) {
                    $package->departures()->firstOrCreate(['starts_on' => $date]);
                }
            } else {
                $package->departures()->where('seats_booked', 0)->delete();
            }

            if ($this->removed) {
                $package->media()->whereIn('id', $this->removed)->get()->each->delete();
            }

            foreach ($this->photos as $photo) {
                $package->addMedia($photo->getRealPath())
                    ->usingFileName(Str::random(10) . '.' . $photo->extension())
                    ->preservingOriginal()
                    ->toMediaCollection('photos');
            }
        });
    }

    private function rulesFor(int $step): array
    {
        $trip = $this->type === 'trip';

        return match ($step) {
            0 => [
                'type' => ['required', Rule::enum(PackageType::class)],
                'title' => 'required|string|min:5|max:120',
                'destination_id' => 'required|exists:destinations,id',
                'category_id' => 'required|exists:categories,id',
                'summary' => 'nullable|string|max:160',
                'description' => 'required|string|min:30|max:5000',
            ],
            1 => [
                'included' => 'array',
                'included.*' => Rule::in(self::INCLUSIONS),
                'excluded' => 'nullable|string|max:1000',
                ...($trip ? [
                    'plan' => 'required|array|min:1|max:30',
                    'plan.*' => 'required|string|max:1000',
                ] : [
                    'duration_hours' => 'required|numeric|min:0.5|max:24',
                    'start_time' => 'required|date_format:H:i',
                    'meeting_point' => 'required|string|max:160',
                    'bring' => 'nullable|string|max:160',
                ]),
            ],
            2 => [
                'price' => 'required|integer|min:100|max:10000000',
                'child_discount_percent' => 'required|integer|min:0|max:100',
                'max_group_size' => 'nullable|integer|min:1|max:500',
                'cancellation_policy' => ['required', Rule::enum(CancellationPolicy::class)],
                ...($trip ? [
                    'departures' => 'required|array|min:1',
                    'departures.*' => 'required|date|after_or_equal:today',
                ] : [
                    'weekdays' => 'required|array|min:1',
                    'weekdays.*' => Rule::in(self::DAYS),
                ]),
            ],
            3 => [
                'photos' => 'array|max:12',
                'photos.*' => 'image|max:5120',
                'video_url' => 'nullable|url|max:255',
            ],
        };
    }

    protected function validationAttributes(): array
    {
        return [
            'plan.*' => 'day description',
            'departures.*' => 'departure date',
            'destination_id' => 'destination',
            'category_id' => 'category',
            'max_group_size' => 'group size',
            'child_discount_percent' => 'child discount',
        ];
    }

    public function render()
    {
        $destinations = Destination::orderBy('name')->get(['id', 'name']);

        return view('livewire.operators.package-form', [
            'destinations' => $destinations,
            'categories' => Category::orderBy('sort_order')->get(['id', 'name']),
            'placeName' => $destinations->firstWhere('id', $this->destination_id)?->name ?? 'Destination',
            'verified' => auth()->user()->operatorProfile?->status === 'verified',
            'policies' => CancellationPolicy::cases(),
            'feePercent' => self::FEE_PERCENT,
        ]);
    }
}
