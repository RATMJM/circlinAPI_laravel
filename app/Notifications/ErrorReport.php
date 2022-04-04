<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\SlackMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Throwable;

class ErrorReport extends Notification
{
    use Queueable;

    private Throwable $e;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Throwable $e)
    {
        $this->e = $e;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     *
     * @return array
     */
    public function via($notifiable)
    {
        return ['slack'];
    }

    public function toSlack($notifiable)
    {
        $user = User::find('id', token_option()?->uid ?? null) ?? null;
        $request = request();

        $e = $this->e;

        $message = $e->getMessage();

        $uri = explode('?', $request->getRequestUri())[0];

        $payload = '';
        foreach ($request->all() as $key => $item) {
            $payload .= "$key : $item\n";
        }

        $content = "*써클인 API 에서 오류가 발생했습니다.*
사용자 IP : `{$request->ip()}`
닉네임 (ID) : `{$user?->nickname} ({$user?->id})`
API URL : `{$request->method()} $uri`
Payload : ``` $payload ```
```$message```";

        return (new SlackMessage())
            ->from('써클인 API', ':red_circle:')
            ->to('#circlin-log')
            // ->image('https://www.circlin.co.kr/new/assets/favicon/apple-icon-180x180.png')
            ->content($content);
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable
     *
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage())
            ->line('The introduction to the notification . ')
            ->action('Notification Action', url(' / '))
            ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param mixed $notifiable
     *
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
