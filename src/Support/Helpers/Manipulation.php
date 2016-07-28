<?php

if (! function_exists('api_transform')) {
    /**
     * Transform content with an API transformer.
     *
     * @author Morten Rugaard <moru@nodes.dk>
     *
     * @param mixed                                      $content
     * @param \Nodes\Api\Transformer\TransformerAbstract $transformer
     * @param array                                      $parameters
     * @param \Closure|null                              $after
     * @return array
     */
    function api_transform($content, \Nodes\Api\Transformer\TransformerAbstract $transformer, array $parameters = [], \Closure $after = null)
    {
        // Retrieve transformer factory
        $transformerFactory = app('api.transformer');

        // Register binding resolver
        $transformerFactory->register(get_class($content), $transformer, $parameters, $after);

        // Transform content
        return $transformerFactory->transform($content);
    }
}
