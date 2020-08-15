<?php

namespace Valet;

use Illuminate\Container\Container;
use Silly\Application;
use Symfony\Component\Console\Question\ConfirmationQuestion;

class Prompt
{

    public function yesNoQuestion(Application $app, $input, $output, $question, $default = false)
    {
        $helper = $app->getHelperSet()->get('question');
        $question = new ConfirmationQuestion($question, $default);
        return $helper->ask($input, $output, $question);
    }
}