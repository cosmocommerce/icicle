<?php

/*
 * This file is part of Icicle, a library for writing asynchronous code in PHP using promises and coroutines.
 *
 * @copyright 2014-2015 Aaron Piotrowski. All rights reserved.
 * @license MIT See the LICENSE file that was distributed with this source code for more information.
 */

namespace Icicle\Loop;

use Icicle\Loop\Events\EventFactoryInterface;
use Icicle\Loop\Exception\UnsupportedError;
use Icicle\Loop\Manager\Uv\{SignalManager, SocketManager, TimerManager};
use Icicle\Loop\Manager\{SignalManagerInterface, SocketManagerInterface, TimerManagerInterface};

/**
 * Uses the UV extension to poll sockets for I/O and create timers.
 */
class UvLoop extends AbstractLoop
{
    /**
     * @var resource A uv_loop handle created with uv_loop_new().
     */
    private $loopHandle;

    /**
     * @param bool $enableSignals True to enable signal handling, false to disable.
     * @param \Icicle\Loop\Events\EventFactoryInterface|null $eventFactory
     * @param resource|null $loop Resource created by uv_loop_new() or null to create a new event loop.
     *
     * @throws \Icicle\Loop\Exception\UnsupportedError If the uv extension is not loaded.
     */
    public function __construct($enableSignals = true, EventFactoryInterface $eventFactory = null, $loop = null)
    {
        // @codeCoverageIgnoreStart
        if (!extension_loaded('uv')) {
            throw new UnsupportedError(__CLASS__ . ' requires the UV extension.');
        } // @codeCoverageIgnoreEnd

        // @codeCoverageIgnoreStart
        if (!is_resource($loop)) {
            $this->loopHandle = \uv_loop_new();
        } else { // @codeCoverageIgnoreEnd
            $this->loopHandle = $loop;
        }

        parent::__construct($enableSignals, $eventFactory);
    }

    /**
     * Gets the libuv loop handle.
     *
     * @return resource
     *
     * @internal
     * @codeCoverageIgnore
     */
    public function getLoopHandle()
    {
        return $this->loopHandle;
    }

    /**
     * {@inheritdoc}
     */
    public function reInit()
    {
        // libuv handles forks automatically.
    }

    /**
     * Destroys the UV loop handle when the loop is destroyed.
     */
    public function __destruct()
    {
        if (is_resource($this->loopHandle)) {
            \uv_loop_delete($this->loopHandle);
            $this->loopHandle = null;
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function dispatch(bool $blocking)
    {
        \uv_run($this->loopHandle, $blocking ? \UV::RUN_ONCE : \UV::RUN_NOWAIT);
    }

    /**
     * {@inheritdoc}
     */
    protected function createPollManager(EventFactoryInterface $factory): SocketManagerInterface
    {
        return new SocketManager($this, $factory, \UV::READABLE);
    }

    /**
     * {@inheritdoc}
     */
    protected function createAwaitManager(EventFactoryInterface $factory): SocketManagerInterface
    {
        return new SocketManager($this, $factory, \UV::WRITABLE);
    }

    /**
     * {@inheritdoc}
     */
    protected function createTimerManager(EventFactoryInterface $factory): TimerManagerInterface
    {
        return new TimerManager($this, $factory);
    }

    /**
     * {@inheritdoc}
     */
    protected function createSignalManager(EventFactoryInterface $factory): SignalManagerInterface
    {
        return new SignalManager($this, $factory);
    }
}