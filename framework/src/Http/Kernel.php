<?php
namespace Gerald\Framework\Http;

class Kernel
{
    public function handle(Request $request)
    {
        $content = '<h1>Hello World</h1>';
        return new Response($content);
    }
}