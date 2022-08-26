<?php

namespace App\Trading\Telegram;

use Exception;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use NotificationChannels\Telegram\Exceptions\CouldNotSendNotification;
use NotificationChannels\Telegram\Telegram;
use Psr\Http\Message\ResponseInterface;

class Client extends Telegram
{
    protected int $retryMax = 5;

    protected int $retryCount = 0;

    protected function retryReset(): void
    {
        $this->retryCount = 0;
    }

    protected function retried(): bool
    {
        return ++$this->retryCount < $this->retryMax;
    }

    /**
     * @throws CouldNotSendNotification
     * @throws GuzzleException
     */
    protected function sendRequest(string $endpoint, array $params, bool $multipart = false): ?ResponseInterface
    {
        if (blank($this->token)) {
            throw CouldNotSendNotification::telegramBotTokenNotProvided('You must provide your telegram bot token to make any API requests.');
        }

        $apiUri = sprintf('%s/bot%s/%s', $this->apiBaseUri, $this->token, $endpoint);

        try {
            return take(
                $this->httpClient()->post($apiUri, [
                    $multipart ? 'multipart' : 'form_params' => $params,
                ]),
                fn() => $this->retryReset()
            );
        }
        catch (ClientException $exception) {
            if (($response = $exception->getResponse())->getStatusCode() === 429 // too many requests
                && $this->retried()) {
                // wait
                sleep(
                    1
                    + data_get(
                        json_decode_array($response->getBody()->getContents()),
                        'parameters.retry_after',
                        0
                    )
                );
                // then try again
                return $this->sendRequest($endpoint, $params, $multipart);
            }
            $this->retryReset();
            throw CouldNotSendNotification::telegramRespondedWithAnError($exception);
        }
        catch (Exception $exception) {
            $this->retryReset();
            throw CouldNotSendNotification::couldNotCommunicateWithTelegram($exception);
        }
    }
}
