<?php
 /*
 * Copyright 2011 Marcelo Gornstein <marcelog@gmail.com>
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
use Ding\Helpers\ErrorHandler\ErrorInfo;
use Ding\Helpers\ErrorHandler\IErrorHandler;
use Ding\Helpers\SignalHandler\ISignalHandler;
use Ding\Helpers\ShutdownHandler\IShutdownHandler;

/**
 * @ErrorHandler
 * @SignalHandler
 * @ShutdownHandler
 * @InitMethod(method=anInitMethod)
 * @DestroyMethod(method=aDestroyMethod)
 */
class MyErrorHandler implements IErrorHandler, ISignalHandler, IShutdownHandler
{
    public function anInitMethod()
    {
        echo "Hello, this is the init method of your errorhandler\n";
    }

    public function aDestroyMethod()
    {
        echo "Hello, this is the *destroy* method of your errorhandler\n";
    }

    public function handleError(ErrorInfo $error)
    {
        echo "This is your custom error handler: " . print_r($error, true);
    }

    public function handleShutdown()
    {
        echo "This is your custom shutdown handler.\n";
    }

    public function handleSignal($signal)
    {
        echo "This is your custom signal handler: " . $signal . "\n";
    }
}
