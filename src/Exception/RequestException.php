<?php

/*
 * This file is part of Guzzle HTTP JSON-RPC
 *
 * Copyright (c) 2014 Nature Delivered Ltd. <http://graze.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @see  http://github.com/graze/guzzle-jsonrpc/blob/master/LICENSE
 * @link http://github.com/graze/guzzle-jsonrpc
 */

namespace Graze\GuzzleHttp\JsonRpc\Exception;

use Graze\GuzzleHttp\JsonRpc\Message\RequestInterface;
use Graze\GuzzleHttp\JsonRpc\Message\ResponseInterface;
use GuzzleHttp\BodySummarizerInterface;
use GuzzleHttp\Exception\RequestException as HttpRequestException;
use Psr\Http\Message\RequestInterface as HttpRequestInterface;
use Psr\Http\Message\ResponseInterface as HttpResponseInterface;

class RequestException extends HttpRequestException
{
    /**
     * {@inheritdoc}
     *  Factory method to create a new exception with a normalized error message
     *
     * @param HttpRequestInterface $request Request sent
     * @param HttpResponseInterface $response Response received
     * @param \Throwable|null $previous Previous exception
     * @param array $handlerContext Optional handler context
     * @param BodySummarizerInterface|null $bodySummarizer Optional body summarizer
     */
    public static function create(
        HttpRequestInterface $request,
        HttpResponseInterface $response = null,
        \Throwable $previous = null,
        array $handlerContext = [],
        BodySummarizerInterface $bodySummarizer = null
    ): self {
        if ($request instanceof RequestInterface && $response instanceof ResponseInterface) {
            static $clientErrorCodes = [-32600, -32601, -32602, -32700];

            $errorCode = $response->getRpcErrorCode();
            if (in_array($errorCode, $clientErrorCodes)) {
                $label = 'Client RPC error response';
                $className = ClientException::class;
            } else {
                $label = 'Server RPC error response';
                $className = ServerException::class;
            }

            $message = $label . ' [uri] ' . $request->getRequestTarget()
                . ' [method] ' . $request->getRpcMethod()
                . ' [error code] ' . $errorCode
                . ' [error message] ' . $response->getRpcErrorMessage();

            return new $className($message, $request, $response, $previous);
        }

        return parent::create($request, $response, $previous);
    }
}
