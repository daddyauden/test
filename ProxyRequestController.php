<?php

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use GuzzleHttp\Client;

class ProxyRequestController extends Controller
{
    private $client;

    public function __construct()
    {
        $this->client = new Client();
    }

    protected function getRequest()
    {
        return $this->container->get('request_stack')->getCurrentRequest();
    }

    protected function isGet()
    {
        return $this->getRequest()->getMethod() === "GET";
    }

    protected function isPost()
    {
        return $this->getRequest()->getMethod() === "POST";
    }

    private function getRequestData($key = "request", $name)
    {
        if ($name === NULL) {
            return $this->getRequest()->$key->all();
        }

        return $this->getRequest()->$key->get($name);
    }

    protected function getHeader($name = NULL)
    {
        return $this->getRequestData("headers", $name);
    }

    protected function getPost($name = NULL)
    {
        return $this->getRequestData("request", $name);
    }

    protected function getFormData($name = NULL)
    {
        $form = $this->getPost("form");

        if ($name === NULL) {
            return $form;
        }

        return isset($form[$name]) ? $form[$name] : NULL;
    }

    /**
     * @Route("/proxy/{domain}/{method}", name="proxy_handle_requst", requirements={"domain": "\s+", "method": "get|post"}, defaults={"method": "get"})
     */
    public function handleRequstAction($domain, $method)
    {
        $method = strtolower($method);

        $headers = $this->getHeader();

        $headers["user-agent"] = "curl/7.35.0";

        if ("get" == $method) {
            return $this->client->request("get", $domain, ["headers" => $headers]);
        } else if ("post" == $method) {
            $formData = $this->getFormData();

            return $this->client->request("post", $domain, [
                "headers" => $headers,
                "body" => $formData
            ]);
        }
    }
}