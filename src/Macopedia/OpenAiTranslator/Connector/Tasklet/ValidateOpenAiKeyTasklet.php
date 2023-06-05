<?php

declare(strict_types=1);

namespace Macopedia\OpenAiTranslator\Connector\Tasklet;

use Akeneo\Tool\Component\Batch\Job\BatchStatus;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\Step\TaskletInterface;
use Exception;

class ValidateOpenAiKeyTasklet implements TaskletInterface
{
    private StepExecution $stepExecution;

    public function __construct(
        private ?string $openAiKey
    ) {
    }

    public function execute(): void
    {
        if (empty($this->openAiKey)) {
            $this->stepExecution->addFailureException(new Exception('OpenAI key is not set'));
            $this->stepExecution->setStatus(new BatchStatus(BatchStatus::FAILED));
        }
    }

    public function setStepExecution(StepExecution $stepExecution): void
    {
        $this->stepExecution = $stepExecution;
    }
}
