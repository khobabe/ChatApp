<?php

namespace App\Livewire;

use App\Events\messageSendEvent;
use App\Models\Message;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;

class ChatComponent extends Component
{
    public $user;
    public $sender_id;
    public $reciever_id;
    public $message = '';
    public $messages = [];

    public function render()
    {
        return view('livewire.chat-component');
    }

    public function mount($user_id)
    {

        $this->sender_id = Auth::id();
        // $this->sender_id = auth()->user()->id();
        $this->reciever_id = $user_id;

        $messages = Message::where(function ($query) {
            $query->where('sender_id', $this->sender_id)->where('reciever_id', $this->reciever_id);
        })->orWhere(function ($query) {
            $query->where('sender_id', $this->reciever_id)->where('reciever_id', $this->sender_id);
        })->with('sender:id,name', 'reciever:id,name')->get();

        // dd($messages->toArray());

        foreach ($messages as $message) {
            $this->appendChatMessage($message);
        }

        // dd($this->messages);

        $this->user = User::whereId($user_id)->first();
    }

    public function sendMessage()
    {
        // dd($this->message);
        $chatMessage = new Message();

        $chatMessage->sender_id = $this->sender_id;
        $chatMessage->reciever_id = $this->reciever_id;
        $chatMessage->message = $this->message;
        $chatMessage->save();

        $this->appendChatMessage($chatMessage);
        broadcast(new messageSendEvent($chatMessage))->toOthers();

        $this->message = '';
    }

    #[On('echo-private:chat-channel.{sender_id},messageSendEvent')]
    public function listenForMessage($event)
    {
        $chatMessage = Message::whereId($event['message']['id'])
            ->with('sender:id,name', 'reciever:id,name')->get()
            ->first();
        $this->appendChatMessage($chatMessage);
    }

    public function appendChatMessage($message)
    {
        $this->messages[] = [
            'id' => $message->id,
            'message' => $message->message,
            'sender' => $message->sender->name,
            'reciever' => $message->reciever->name
        ];
    }
}
