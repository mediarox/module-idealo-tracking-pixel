<?php
/**
 * @package   Mediarox_IdealoTrackingPixel
 * @copyright Copyright 2020 (c) mediarox UG (haftungsbeschraenkt) (http://www.mediarox.de)
 * @author    Marcus Bernt <mbernt@mediarox.de>
 */

namespace Mediarox\IdealoTrackingPixel\Plugin;

use Magento\Checkout\Model\Session;

/**
 * Class FrontControllerPlugin
 */
class FrontControllerPlugin
{
    /**
     * @var Session
     */
    private $session;

    /**
     * FrontControllerPlugin constructor.
     *
     * @param Session $session
     */
    public function __construct(Session $session)
    {
        $this->session = $session;
    }

    public function beforeDispatch(\Magento\Framework\App\FrontController $subject, $request)
    {
        $idealoId = $request->getParam('idealoid');
        if ($idealoId) {
            $this->session->setIdealoId($idealoId);
        }
        return null;
    }
}
