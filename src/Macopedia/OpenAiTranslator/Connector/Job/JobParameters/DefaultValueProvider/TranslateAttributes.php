<?php

declare(strict_types=1);

namespace Macopedia\OpenAiTranslator\Connector\Job\JobParameters\DefaultValueProvider;

use Akeneo\Tool\Component\Batch\Job\JobInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters\DefaultValuesProviderInterface;

class TranslateAttributes implements DefaultValuesProviderInterface
{
    /**
     * @param array<string> $supportedJobNames
     */
    public function __construct(
        private array $supportedJobNames,
        private ?string $openAiKey
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultValues(): array
    {
        return [
            'filters' => [],
            'actions' => [],
            'realTimeVersioning' => true,
            'users_to_notify' => [],
            'is_user_authenticated' => false,
            'open_ai_key' => $this->openAiKey ?? ''
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function supports(JobInterface $job): bool
    {
        return in_array($job->getName(), $this->supportedJobNames);
    }
}