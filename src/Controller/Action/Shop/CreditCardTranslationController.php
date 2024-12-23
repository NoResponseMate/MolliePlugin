<?php

namespace SyliusMolliePlugin\Controller\Action\Shop;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;

final class CreditCardTranslationController
{
    /** @var TranslatorInterface */
    private $translator;

    /**
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator) {
        $this->translator = $translator;
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function fetchTranslations(Request $request): JsonResponse
    {
        $response = [
            'emptyCardHolder' => $this->translator->trans('sylius_mollie_plugin.ui.credit_card_validations.empty_card_holder'),
            'emptyCardNumber' => $this->translator->trans('sylius_mollie_plugin.ui.credit_card_validations.empty_card_number'),
            'emptyExpiryDate' => $this->translator->trans('sylius_mollie_plugin.ui.credit_card_validations.empty_expiry_date'),
            'emptyVerificationCode' => $this->translator->trans('sylius_mollie_plugin.ui.credit_card_validations.empty_verification_code'),
            'oneOrMoreInvalidFields' => $this->translator->trans('sylius_mollie_plugin.ui.credit_card_validations.one_or_more_invalid_fields'),
        ];

        return new JsonResponse(['status' => Response::HTTP_OK, 'translations' => $response]);
    }
}
