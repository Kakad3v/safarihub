<?php

namespace App\Livewire\Memories;

use App\Models\Memory;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Shared extends Component
{
    public Memory $memory;

    public function mount(Memory $memory): void
    {
        abort_unless($memory->share_token, 404);

        $this->memory = $memory->load(['user:id,name', 'package:id,title,slug']);
    }

    public function render()
    {
        return view('livewire.memories.shared')->title('A photo shared on SafariHub');
    }
}
