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
        if (is_null($command = $this->parseCommandFromMessageText($request->input('message.text')))) {
            report(new InvalidArgumentException('Message is invalid.'));
            return $this->responseContent($request);
        }
        try {
            return $this->responseContent(
                $request,
                $this->execute($request, $this->transform($request, $command))
            );
        }
        catch (Throwable $throwable) {
            report($throwable);
            return $this->responseContent($request);
        }
    }

    protected function parseCommandFromMessageText(?string $messageText): ?string
    {
        if (is_null($messageText)) {
            return null;
        }
        $messageText = trim($messageText);
        if ($messageText[0] != '/') {
            return null;
        }
        $messageText = mb_substr($messageText, 1);
        if ($messageText[0] == ' ') {
            return null;
        }
        return 'telegram:' . $messageText;
    }

    protected function transform(Request $request, string $command): string
    {
        if ($command == 'telegram:hello') {
            $command = 'telegram:ping';
        }
        return $command . sprintf(' --telegram-update=\'%s\'', $request->getContent());
    }

    protected function execute(Request $request, string $command): string
    {
        Artisan::call($command);
        return Artisan::output();
    }
}
