<?php

namespace Gracious\Interconnect\Api;

use Mockery;
use Magento\Newsletter\Model\Subscriber;

class CopernicaTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Mockery\MockInterface
     */
    private $config;

    /**
     * @var \Mockery\MockInterface
     */
    private $request;

    /**
     * @var \Mockery\MockInterface
     */
    private $customerRepository;

    /**
     * @var \Mockery\MockInterface
     */
    private $subscriberFactory;

    /**
     * @var Copernica
     */
    private $object;

    const validSubscribeJson = '{"action":"update","profile":123,"parameters":{"email":"roni_cost@example.com"},"timestamp":"1979-02-12 12:49:23","id":123,"database":1,"fields":{"name":"roni","email":"roni_cost@example.com","newsletter":"subscribed"},"interests":{"blue":1,"red":0},"created":"1979-02-12 12:49:23","modified":"1979-02-12 12:49:23"}';
    const validUnsubscribeJson = '{"action":"update","profile":123,"parameters":{"email":"roni_cost@example.com"},"timestamp":"1979-02-12 12:49:23","id":123,"database":1,"fields":{"name":"roni","email":"roni_cost@example.com","newsletter":"unsubscribed"},"interests":{"blue":1,"red":0},"created":"1979-02-12 12:49:23","modified":"1979-02-12 12:49:23"}';
    const invalidActionJson = '{"action":"create","profile":123,"parameters":{"email":"roni_cost@example.com"},"timestamp":"1979-02-12 12:49:23","id":123,"database":1,"fields":{"name":"roni","email":"roni_cost@example.com","newsletter":"unsubscribed"},"interests":{"blue":1,"red":0},"created":"1979-02-12 12:49:23","modified":"1979-02-12 12:49:23"}';
    const nonNewsletterJson = '{"action":"create","profile":123,"parameters":{"email":"roni_cost@example.com"},"timestamp":"1979-02-12 12:49:23","id":123,"database":1,"fields":{"name":"roni","email":"roni_cost@example.com"},"interests":{"blue":1,"red":0},"created":"1979-02-12 12:49:23","modified":"1979-02-12 12:49:23"}';

    public function setUp()
    {
        $this->config = Mockery::mock(\Gracious\Interconnect\Helper\Config::class )->shouldIgnoreMissing();
        $this->request = Mockery::mock(\Magento\Framework\App\Request\Http::class )->shouldIgnoreMissing();
        $this->customerRepository = Mockery::mock(\Magento\Customer\Api\CustomerRepositoryInterface::class )->shouldIgnoreMissing();
        $this->subscriberFactory = Mockery::mock(\Magento\Newsletter\Model\SubscriberFactory::class )->shouldIgnoreMissing();
        $this->object = new Copernica($this->config,$this->request, $this->customerRepository, $this->subscriberFactory);
    }

    public function tearDown()
    {
        Mockery::close();
    }

    public function test_it_can_subscribe_a_person()
    {
        $this->mockXsecretCheck();

        $this->request->expects()->getContent()->andReturns(self::validSubscribeJson);

        $subscriberModel = Mockery::mock(\Magento\Newsletter\Model\Subscriber::class )->shouldIgnoreMissing();
        $subscriberModel->expects()->subscribe('roni_cost@example.com')->once();
        $subscriberModel->expects()->getId()->never();
        $subscriberModel->expects()->getSubscriberStatus()->andReturns(Subscriber::STATUS_UNSUBSCRIBED);
        $subscriberModel->expects()->loadByEmail('roni_cost@example.com')->once()->andReturnSelf();

        $this->subscriberFactory->expects()->create()->once()->andReturns($subscriberModel);
        
        $return = $this->object->updateProfile();
        $this->assertTrue($return);
    }

    public function test_it_can_unsubscribe_a_person()
    {
        $this->mockXsecretCheck();

        $this->request->expects()->getContent()->andReturns(self::validUnsubscribeJson);

        $subscriberModel = Mockery::mock(\Magento\Newsletter\Model\Subscriber::class )->shouldIgnoreMissing();
        $subscriberModel->expects()->unsubscribe('roni_cost@example.com')->once();
        $subscriberModel->expects()->getId()->once()->andReturns(1);
        $subscriberModel->expects()->getSubscriberStatus()->andReturns(Subscriber::STATUS_SUBSCRIBED);
        $subscriberModel->expects()->loadByEmail('roni_cost@example.com')->once()->andReturnSelf();

        $this->subscriberFactory->expects()->create()->once()->andReturns($subscriberModel);

        $return = $this->object->updateProfile();
        $this->assertTrue($return);
    }

    public function test_it_throws_an_exception_with_invalid_x_secret()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Invalid or empty X-Secret sent');

        $this->config->expects()->getApiKey()->once()->andReturns('WRONG');
        $this->request->expects()->getHeader('X-Secret')->andReturns('1234567890');

        $return = $this->object->updateProfile();
        $this->assertTrue($return);
    }

    public function test_it_only_accepts_updates()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Only updates are accepted');

        $this->request->expects()->getContent()->andReturns(self::invalidActionJson);

        $this->mockXsecretCheck();

        $return = $this->object->updateProfile();
        $this->assertTrue($return);
    }

    public function test_it_wont_subscribe_someone_already_subscribed()
    {
        $this->mockXsecretCheck();

        $this->request->expects()->getContent()->andReturns(self::validSubscribeJson);

        $subscriberModel = Mockery::mock(\Magento\Newsletter\Model\Subscriber::class )->shouldIgnoreMissing();
        $subscriberModel->expects()->subscribe('roni_cost@example.com')->never();
        $subscriberModel->expects()->getId()->never();
        $subscriberModel->expects()->getSubscriberStatus()->andReturns(Subscriber::STATUS_SUBSCRIBED);
        $subscriberModel->expects()->loadByEmail('roni_cost@example.com')->once()->andReturnSelf();

        $this->subscriberFactory->expects()->create()->once()->andReturns($subscriberModel);

        $return = $this->object->updateProfile();
        $this->assertFalse($return);
    }

    public function test_it_wont_unsubscribe_someone_already_unsubscribed()
    {
        $this->mockXsecretCheck();

        $this->request->expects()->getContent()->andReturns(self::validUnsubscribeJson);

        $subscriberModel = Mockery::mock(\Magento\Newsletter\Model\Subscriber::class )->shouldIgnoreMissing();
        $subscriberModel->expects()->unsubscribe('roni_cost@example.com')->never();
        $subscriberModel->expects()->getId()->once()->andReturns(1);
        $subscriberModel->expects()->getSubscriberStatus()->andReturns(Subscriber::STATUS_UNSUBSCRIBED);
        $subscriberModel->expects()->loadByEmail('roni_cost@example.com')->once()->andReturnSelf();

        $this->subscriberFactory->expects()->create()->once()->andReturns($subscriberModel);

        $return = $this->object->updateProfile();
        $this->assertFalse($return);
    }

    private function mockXsecretCheck()
    {
        $this->config->expects()->getApiKey()->once()->andReturns('1234567890');
        $this->request->expects()->getHeader('X-Secret')->once()->andReturns('1234567890');
    }
}
