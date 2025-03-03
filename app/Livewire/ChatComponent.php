<?php

namespace App\Livewire;

use App\Models\Message;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ChatComponent extends Component
{
    public $user;
    public $sender_id;
    public $reciever_id;
    public $message = '';

    public function render()
    {
        return view('livewire.chat-component');
    }

    public function mount($user_id){

        $this->sender_id = Auth::id();
        // $this->sender_id = auth()->user()->id();
        $this->reciever_id = $user_id;

        $this->user = User::whereId($user_id)->first();
    }

    public function sendMessage(){
        // dd($this->message);
        $chatMessage = new Message();

        $chatMessage->sender_id = $this->sender_id;
        $chatMessage->reciever_id = $this->reciever_id;
        $chatMessage->message = $this->message;
        $chatMessage->save();
        
        $this->message = '';
    }
}
