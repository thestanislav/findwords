<?php

namespace ExprAs\Nutgram\Console;

#[AsCommand(
    name: 'nutgram:make:handler',
    description: 'Create a new Nutgram Handler',
)]
class MakeHandlerCommand extends BaseMakeCommand
{
    protected function getSubDirName(): string
    {
        return 'Handlers';
    }

    protected function getStubPath(): string
    {
        return __DIR__ . '/../Stubs/Handler.stub';
    }
}
