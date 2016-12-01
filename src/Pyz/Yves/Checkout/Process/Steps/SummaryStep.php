<?php

/**
 * This file is part of the Spryker Demoshop.
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Pyz\Yves\Checkout\Process\Steps;

use Pyz\Yves\Cart\Grouper\CartItemGouperInterface;
use Spryker\Client\Calculation\CalculationClientInterface;
use Spryker\Shared\Transfer\AbstractTransfer;
use Symfony\Component\HttpFoundation\Request;

class SummaryStep extends AbstractBaseStep
{

    /**
     * @var \Spryker\Client\Calculation\CalculationClient
     */
    protected $calculationClient;

    /**
     * @var \Pyz\Yves\Cart\Grouper\CartItemGouper
     */
    protected $cartItemGouper;

    /**
     * @param \Spryker\Client\Calculation\CalculationClientInterface $calculationClient
     * @param \Pyz\Yves\Cart\Grouper\CartItemGouperInterface $cartItemGouper
     * @param string $stepRoute
     * @param string $escapeRoute
     */
    public function __construct(
        CalculationClientInterface $calculationClient,
        CartItemGouperInterface $cartItemGouper,
        $stepRoute,
        $escapeRoute
    ) {
        parent::__construct($stepRoute, $escapeRoute);

        $this->calculationClient = $calculationClient;
        $this->cartItemGouper = $cartItemGouper;
    }

    /**
     * @param \Spryker\Shared\Transfer\AbstractTransfer $quoteTransfer
     *
     * @return bool
     */
    public function requireInput(AbstractTransfer $quoteTransfer)
    {
        return true;
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \Spryker\Shared\Transfer\AbstractTransfer|\Generated\Shared\Transfer\QuoteTransfer $quoteTransfer
     *
     * @return \Generated\Shared\Transfer\QuoteTransfer
     */
    public function execute(Request $request, AbstractTransfer $quoteTransfer)
    {
        return $this->calculationClient->recalculate($quoteTransfer);
    }

    /**
     * @param \Spryker\Shared\Transfer\AbstractTransfer|\Generated\Shared\Transfer\QuoteTransfer $quoteTransfer
     *
     * @return bool
     */
    public function postCondition(AbstractTransfer $quoteTransfer)
    {
        if ($quoteTransfer->getBillingAddress() === null
            || $quoteTransfer->getShipment() === null
            || $quoteTransfer->getPayment() === null
            || $quoteTransfer->getPayment()->getPaymentProvider() === null
        ) {
            return false;
        }

        return true;
    }

    /**
     * @param \Spryker\Shared\Transfer\AbstractTransfer|\Generated\Shared\Transfer\QuoteTransfer $quoteTransfer
     *
     * @return array
     */
    public function getTemplateVariables(AbstractTransfer $quoteTransfer)
    {
        return [
            'quoteTransfer' => $quoteTransfer,
            'cartItems' => $this->cartItemGouper->groupCartItems($quoteTransfer),
        ];
    }

}
