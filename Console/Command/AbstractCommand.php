<?php
/**
 * Magento 2 Inxmail Module
 *
 * @link http://flagbit.de
 * @link https://www.inxmail.de/
 * @author Flagbit GmbH
 * @copyright Copyright © 2017-2018 Inxmail GmbH
 * @license Licensed under the Open Software License version 3.0 (https://opensource.org/licenses/OSL-3.0)
 *
 */

namespace Flagbit\Inxmail\Console\Command;

use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Framework\Exception\LocalizedException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class AbstractCommand
 *
 * @package Flagbit\Inxmail\Console\Command
 */
abstract class AbstractCommand extends Command
{
    /** @var State */
    protected $state;

    /**
     * @param State $state
     *
     * @throws \LogicException
     */
    public function __construct(State $state)
    {
        $this->state = $state;

        parent::__construct();
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    abstract protected function _execute(InputInterface $input, OutputInterface $output);

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->setAreaCode(AREA::AREA_FRONTEND);
        $this->_execute($input, $output);
    }

    /**
     * @param string $area
     *
     * @return \Magento\Framework\App\State
     */
    private function setAreaCode($area = Area::AREA_CRONTAB): State
    {
        try {
            $this->state->setAreaCode($area);
        } catch (LocalizedException $e) {
            // do nothing as area code has already been set
        }

        return $this->state;
    }
}
