<?php

namespace Mautic\LeadBundle\Model;

use Mautic\CoreBundle\Model\FormModel;
use Mautic\LeadBundle\Entity\LeadDevice;
use Mautic\LeadBundle\Entity\LeadDeviceRepository;
use Mautic\LeadBundle\Event\LeadDeviceEvent;
use Mautic\LeadBundle\Form\Type\DeviceType;
use Mautic\LeadBundle\LeadEvents;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * @extends FormModel<LeadDevice>
 */
class DeviceModel extends FormModel
{
    /**
     * @var LeadDeviceRepository
     */
    private $leadDeviceRepository;

    /**
     * DeviceModel constructor.
     */
    public function __construct(
        LeadDeviceRepository $leadDeviceRepository
    ) {
        $this->leadDeviceRepository = $leadDeviceRepository;
    }

    /**
     * {@inheritdoc}
     *
     * @return LeadDeviceRepository
     */
    public function getRepository()
    {
        return $this->leadDeviceRepository;
    }

    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public function getPermissionBase()
    {
        return 'lead:leads';
    }

    /**
     * Get a specific entity or generate a new one if id is empty.
     *
     * @param $id
     *
     * @return object|null
     */
    public function getEntity($id = null)
    {
        if (null === $id) {
            return new LeadDevice();
        }

        return parent::getEntity($id);
    }

    /**
     * {@inheritdoc}
     *
     * @param       $entity
     * @param null  $action
     * @param array $options
     *
     * @return mixed
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function createForm($entity, FormFactoryInterface $formFactory, $action = null, $options = [])
    {
        if (!$entity instanceof LeadDevice) {
            throw new MethodNotAllowedHttpException(['LeadDevice']);
        }

        if (!empty($action)) {
            $options['action'] = $action;
        }

        return $formFactory->create(DeviceType::class, $entity, $options);
    }

    /**
     * {@inheritdoc}
     *
     * @param $action
     * @param $event
     * @param $entity
     * @param $isNew
     *
     * @throws \Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException
     */
    protected function dispatchEvent($action, &$entity, $isNew = false, Event $event = null)
    {
        if (!$entity instanceof LeadDevice) {
            throw new MethodNotAllowedHttpException(['LeadDevice']);
        }

        switch ($action) {
            case 'pre_save':
                $name = LeadEvents::DEVICE_PRE_SAVE;
                break;
            case 'post_save':
                $name = LeadEvents::DEVICE_POST_SAVE;
                break;
            case 'pre_delete':
                $name = LeadEvents::DEVICE_PRE_DELETE;
                break;
            case 'post_delete':
                $name = LeadEvents::DEVICE_POST_DELETE;
                break;
            default:
                return null;
        }

        if ($this->dispatcher->hasListeners($name)) {
            if (empty($event)) {
                $event = new LeadDeviceEvent($entity, $isNew);
                $event->setEntityManager($this->em);
            }

            $this->dispatcher->dispatch($event, $name);

            return $event;
        } else {
            return null;
        }
    }
}
