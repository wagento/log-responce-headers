<?php


namespace Wagento\OriginHeaders\Observer;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Psr\Log\LoggerInterface;

class LogOriginHeadersObserver implements ObserverInterface
{
    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @var ScopeConfigInterface
     */
    private ScopeConfigInterface $scopeConfig;

    /**
     * @param LoggerInterface $logger
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        LoggerInterface $logger,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->logger = $logger;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Observer for controller_front_send_response_before
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $event = $observer->getEvent();
        $request = $event->getData('request');
        $responseHeaders = $event->getData('response')->getHeaders();
        $enable_log = $this->scopeConfig->getValue('wagento/origin_headers/enable_log', ScopeConfigInterface::SCOPE_TYPE_DEFAULT);
        $enable_browser = $this->scopeConfig->getValue('wagento/origin_headers/enable_browser', ScopeConfigInterface::SCOPE_TYPE_DEFAULT);

        if ($enable_log) {

            // log origin headers to debug.log

            $this->logger->debug(
                "\n"
                . 'Request headers: ' . "\n"
                . $request . "\n"
                . 'Response headers:' . "\n"
                . $responseHeaders->toString()
            );
        }

        if ($enable_browser) {

            // add origin headers to response
            $newResponseHeaderArray = [];
            foreach ($responseHeaders->toArray() as $headerKey => $headerValue) {
                $newResponseHeaderArray['origin-response-' . $headerKey] = $headerValue;
            };
            $responseHeaders->addHeaders($newResponseHeaderArray);
        }
    }
}
