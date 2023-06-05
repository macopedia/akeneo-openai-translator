<?php

declare(strict_types=1);

namespace Macopedia\OpenAiTranslator\Connector\Job\JobParameters\ConstraintCollectionProvider;

use Akeneo\Tool\Component\Batch\Job\JobInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters\ConstraintCollectionProviderInterface;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\Type;

class TranslateAttributes implements ConstraintCollectionProviderInterface
{
    /**
     * @param array<string> $supportedJobNames
     */
    public function __construct(
        private array $supportedJobNames,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function getConstraintCollection(): Collection
    {
        return new Collection(
            [
                'fields' => [
                    'filters' => new NotNull(),
                    'actions' => new NotNull(),
                    'realTimeVersioning' => new Type('bool'),
                    'users_to_notify' => [
                        new Type('array'),
                        new All(new Type('string')),
                    ],
                    'is_user_authenticated' => new Type('bool'),
                    'open_ai_key' => new NotNull(),
                ]
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function supports(JobInterface $job): bool
    {
        return in_array($job->getName(), $this->supportedJobNames);
    }
}