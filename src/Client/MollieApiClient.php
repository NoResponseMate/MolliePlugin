<?php

/*
 * This file is part of the Sylius Mollie Plugin package.
 *
 * (c) Sylius Sp. z o.o.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace SyliusMolliePlugin\Client;

use SyliusMolliePlugin\SyliusMolliePlugin;
use Mollie\Api\MollieApiClient as BaseMollieApiClient;

class MollieApiClient extends BaseMollieApiClient
{
    protected array $config = [];

    protected bool $isRecurringSubscription = false;

    public function getVersion(): string
    {
        return SyliusMolliePlugin::VERSION;
    }

    public function getUserAgentToken(): string
    {
        return SyliusMolliePlugin::USER_AGENT_TOKEN;
    }

    public function setConfig(array $config): void
    {
        $this->config = $config;
    }

    public function getConfig(): array
    {
        return $this->config;
    }

    public function setIsRecurringSubscription(bool $isRecurringSubscription): void
    {
        $this->isRecurringSubscription = $isRecurringSubscription;
    }

    public function isRecurringSubscription(): bool
    {
        return $this->isRecurringSubscription;
    }

    public function getApiKey(): string
    {
        return $this->apiKey;
    }
}
