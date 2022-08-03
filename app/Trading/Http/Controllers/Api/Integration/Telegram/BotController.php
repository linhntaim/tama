<?php

namespace App\Trading\Http\Controllers\Api\Integration\Telegram;

use App\Support\Facades\Artisan;
use App\Support\Http\Controllers\ApiController;
use Illuminate\Http\Request;

class BotController extends ApiController
{
    public function store(Request $request)
    {
        if (is_null($command = $this->parseCommandFromMessageText($request->input('message.text')))) {
            $this->abort404();
        }
        return $this->responseContent(
            $request,
            $this->execute($request, $this->transform($request, $command))
        );
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
