<?php
namespace App\Jobs;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\OmniSetting;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessIncomingMessage implements ShouldQueue
  {
      use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

      public function __construct(protected Message $incomingMessage) {}

      public function handle()
      {
          $conversation = $this->incomingMessage->conversation;
          $companyId = $conversation->company_id;

          $settings = OmniSetting::first();
          if (!$settings) {
              return;
          }

          if ($settings->auto_assign && is_null($conversation->assigned_user_id)) {
              $agents = $settings->auto_assign_agents; // যেমন: [1, 2, 4]

              if (!empty($agents)) {
                  $selectedAgentId = Conversation::whereIn('assigned_user_id', $agents)
                      ->where('company_id', $companyId)
                      ->where('status', '!=', 'closed')
                      ->selectRaw('assigned_user_id, count(*) as active_chats')
                      ->groupBy('assigned_user_id')
                      ->orderBy('active_chats', 'asc')
                      ->first()?->assigned_user_id;

                  if (!$selectedAgentId) {
                      $selectedAgentId = $agents[0];
                  }

            
                  $conversation->update(['assigned_user_id' => $selectedAgentId]);
              }
          }

          $now = Carbon::now('Asia/Dhaka'); // ঢাকার লোকাল টাইম জোন
          $workingHourStart = Carbon::createFromTimeString('10:00:00', 'Asia/Dhaka');
          $workingHourEnd = Carbon::createFromTimeString('22:00:00', 'Asia/Dhaka');

          $isOutsideWorkingHours = !$now->between($workingHourStart, $workingHourEnd);

          if ($isOutsideWorkingHours) {
              if ($settings->away_mode_active && !empty($settings->away_message)) {
                  $this->sendAutomatedResponse($conversation, $settings->away_message);
              }
          } else {
              $previousAgentMessages = $conversation->messages()->where('from', 'agent')->count();

   
              if ($previousAgentMessages === 0 && $settings->welcome_mode_active && !empty($settings->welcome_message)) {
                  $this->sendAutomatedResponse($conversation, $settings->welcome_message);
              }
          }
      }

   
      protected function sendAutomatedResponse(Conversation $conversation, string $text)
      {
          $lastMessage = $conversation->messages()->orderByDesc('created_at')->first();
          if ($lastMessage && $lastMessage->from === 'agent' && $lastMessage->text === $text) {
              return;
          }

          $conversation->messages()->create([
              'from' => 'agent',
              'type' => 'text',
              'text' => $text,
              'is_read' => true,
              'agent_id' => null,
          ]);

          $conversation->update(['last_activity_at' => now()]);

          // TODO: এখানে হোয়াটসঅ্যাপ বা মেটা এপিআই-এর (WhatsApp Service Job) মাধ্যমে কাস্টমারের মোবাইলে মেসেজটি পুশ করা হবে।
      }
  }