<?php
namespace Icicle\Stream;

interface ReadableStreamInterface extends StreamInterface
{
    /**
     * @param   int|null $length Max number of bytes to read. Fewer bytes may be returned. Use null to read as much data as
     *          possible.
     *
     * @return  PromiseInterface
     *
     * @resolve string Data read from the stream.
     *
     * @reject  BusyException If a read was already pending on the stream.
     * @reject  UnreadableException If the stream is no longer readable.
     * @reject  ClosedException If the stream has been closed.
     *
     * @api
     */
    public function read($length = null);
    
    /**
     * @param   string $pattern Reading will stop once the given string of bytes occurs in the stream. Note that reading may
     *          stop before the pattern is found in the stream. The search string will be included in the resolving string.
     * @param   int|null $length Max number of bytes to read. Fewer bytes may be returned. Use null to read as much data as
     *          possible.
     *
     * @return  PromiseInterface
     *
     * @resolve string Data read from the stream (includes the pattern string if found).
     *
     * @reject  BusyException If a read was already pending on the stream.
     * @reject  UnreadableException If the stream is no longer readable.
     * @reject  ClosedException If the stream has been closed.
     *
     * @api
     */
    public function readTo($pattern, $length = null);
    
    /**
     * Returns a promise that is fulfilled when there is data available to read, without actually consuming any data.
     *
     * @return  PromiseInterface
     *
     * @resolve null
     *
     * @reject  BusyException If a read was already pending on the stream.
     * @reject  UnreadableException If the stream is no longer readable.
     * @reject  ClosedException If the stream has been closed.
     *
     * @api
     */
    public function poll();
    
    /**
     * Determines if the stream is still readable. A stream being readable does not mean there is data immediately available
     * to read. Use read() or poll() to wait for data.
     *
     * @return  bool
     *
     * @api
     */
    public function isReadable();
    
    /**
     * Pipes data read on this stream into the given writable stream destination.
     *
     * @param   WritableStreamInterface $stream
     * @param   bool $endOnClose Set to true to automatically end the writable stream when the readable stream closes.
     *
     * @return  PromiseInterface
     *
     * @resolve int Resolves when the writable stream closes with the number of bytes read from this stream.
     *
     * @reject  BusyException If a read was already pending on the stream.
     * @reject  UnreadableException If the stream is no longer readable.
     * @reject  ClosedException If the stream has been closed.
     *
     * @api
     */
    public function pipe(WritableStreamInterface $stream, $endOnClose = true);
}