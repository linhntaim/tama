<?php

namespace App\Trading\Http\Controllers\Api\Integration\Telegram;

use App\Support\Facades\Artisan;
use App\Support\Http\Controllers\ApiController;
use Illuminate\Http\Request;
use InvalidArgumentException;
use Throwable;

class BotController extends ApiController
{
    public function store(Request $request)
    {
        if (!$this->matchSecret($request)) {
            report(new InvalidArgumentException('Secret from header does not match.'));
        }
        elseif (is_null($command = $this->parseCommandFromMessageText(
            $request->input(
                'message.text',
                $request->input(
                    'channel_post.text',
                    $request->input(
                        'edited_message.text',
                        $request->input('edited_channel_post.text')
                    )
                )
            )
        ))) {
            report(new InvalidArgumentException('Message is invalid.'));
        }
        else {
            try {
                $this->execute($request, $this->transform($request, $command));
            }
            catch (Throwable $throwable) {
                report($throwable);
            }
        }
        return $this->responseContent($request);
    }

    protected function matchSecret(Request $request): bool
    {
        return is_null($secret = config('services.telegram-bot-api.webhook_secret'))
            || $request->header('X-Telegram-Bot-Api-Secret-Token') === $secret;
    }

    protected function parseCommandFromMessageText(?string $messageText): ?string
    {
        if (is_null($messageText)) {
            return null;
        }
        $messageText = trim($messageText);
        if ($messageText[0] !== '/') {
            return null;
        }
        $messageText = mb_substr($messageText, 1);
        if ($messageText[0] === ' ') {
            return null;
        }
        $texts = explode(' ', $messageText);
        if (($pos = mb_strpos($texts[0], '@')) !== false) { // handle bot name
            if (!is_null($botName = config('services.telegram-bot-api.username'))
                && mb_substr($texts[0], $pos + 1) !== $botName) {
                return null;
            }
            $texts[0] = explode('@', $texts[0])[0];
        }
        return 'telegram:' . implode(' ', $texts);
    }

    protected function transform(Request $request, string $command): string
    {
        if ($command === 'telegram:hello') {
            $command = 'telegram:ping';
        }
        return $command . sprintf(' --telegram-update=\'%s\'', base64_encode($request->getContent()));
    }

    protected function execute(Request $request, string $command): void
    {
        Artisan::call($command);
    }
}
