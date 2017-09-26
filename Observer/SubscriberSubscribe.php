<?php
namespace Flagbit\Inxmail\Observer;

use \Flagbit\Inxmail\Model\Request;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class SubscriberSubscribe implements ObserverInterface
{
    private $request;
    /** @var \Magento\Customer\Api\CustomerRepository */
    private $customerRepos;

    public function __construct(
        Request $request,
        Request\RequestFactory $factory
    )
    {
        $this->request = $request;
//        $this->customerRepos = $customerRepository;
    }

    public function execute(Observer $observer)
    {

        $subscriber = $observer->getSubscriber();
        if ($subscriber->getCustomerId > 0) {
//            $customer = $this->customerRepos->
        }

        return null;
        // TODO: Implement execute() method.
    }

}
