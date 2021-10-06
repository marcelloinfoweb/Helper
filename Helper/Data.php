<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Funarbe\Helper\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\HTTP\Client\Curl;

class Data extends AbstractHelper
{
    /**
     * @var \Magento\Framework\HTTP\Client\Curl
     */
    protected Curl $curl;

    protected $_logger;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\HTTP\Client\Curl $curl
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Psr\Log\LoggerInterface $logger,
        Curl $curl
    ) {
        $this->_logger = $logger;
        parent::__construct($context);
        $this->curl = $curl;
    }

    /**
     * @param $URL
     * @return mixed|void
     */
    public function curlGet($URL)
    {
        try {
            $this->curl->get($URL);
            $this->curl->setOption(CURLOPT_HEADER, 0);
            $this->curl->setOption(CURLOPT_TIMEOUT, 60);
            $this->curl->setOption(CURLOPT_RETURNTRANSFER, true);
            $this->curl->setOption(CURLOPT_CUSTOMREQUEST, 'GET');

            return json_decode($this->curl->getBody(), true, 512, JSON_THROW_ON_ERROR);

        } catch (\Exception $e) {
            $this->_logger->critical($e);
        }
    }

    /**
     * @return bool
     */
    public function isEnabled(): bool
    {
        return true;
    }

    /**
     * @param $cpf
     * @return mixed|void
     */
    public function getIntegratorRmClienteFornecedor($cpf)
    {
        $URL = "https://integrator2.funarbe.org.br/rm/cliente-fornecedor/";
        $URL .= "?expand=FUNCIONARIOATIVO&fields=FUNCIONARIOATIVO&filter[CGCCFO]=$cpf";

        $username = 'mestre';
        $password = 'cacg93d7';
        $this->_logger->info($URL);

        try {
            //set curl options
            $this->curl->setOption(CURLOPT_USERPWD, $username . ":" . $password);
            $this->curl->setOption(CURLOPT_HEADER, 0);
            $this->curl->setOption(CURLOPT_TIMEOUT, 60);
            $this->curl->setOption(CURLOPT_RETURNTRANSFER, true);
            $this->curl->setOption(CURLOPT_CUSTOMREQUEST, 'GET');

            //get request with url
            $this->curl->get($URL);

            //read response
            $response = $this->curl->getBody();
            $resp = \Safe\json_decode($response, true);

            if (empty($resp['items'])) {
                return false;
            }

            return $resp['items'][0]['FUNCIONARIOATIVO'];

        } catch (\Exception $e) {
            $this->_logger->critical($e);
        }
    }
}
