<?php

namespace Daaner\NovaPoshta\Models;

use Daaner\NovaPoshta\NovaPoshta;
use Daaner\NovaPoshta\Traits\DateTimes;
use Daaner\NovaPoshta\Traits\DocumentList;
use Daaner\NovaPoshta\Traits\InternetDocumentProperty;
use Daaner\NovaPoshta\Traits\Limit;
use Daaner\NovaPoshta\Traits\OptionsSeatProperty;
use Daaner\NovaPoshta\Traits\RecipientProperty;
use Daaner\NovaPoshta\Traits\SenderProperty;

class InternetDocument extends NovaPoshta
{
    /**
     * getDocumentList
     * getMoneyTransferDocuments.
     */
    use Limit, DateTimes;

    /**
     * getDocumentList.
     */
    use DocumentList;

    /**
     * save.
     */
    use InternetDocumentProperty, SenderProperty, OptionsSeatProperty, RecipientProperty;

    protected $model = 'InternetDocument';
    protected $calledMethod;
    protected $methodProperties = [];

    /**
     * Получить список ЭН.
     *
     * @see https://developers.novaposhta.ua/view/model/a90d323c-8512-11ec-8ced-005056b2dbe1/method/a9d22b34-8512-11ec-8ced-005056b2dbe1 Получить список ЭН
     *
     * @return array
     */
    public function getDocumentList(): array
    {
        $this->calledMethod = 'getDocumentList';

        $this->getPage();
        $this->addLimit();

        //DateTime
        $this->getDateTime();
        $this->getDateTimeFromTo();

        return $this->getResponse($this->model, $this->calledMethod, $this->methodProperties);
    }

    /**
     * Создать экспресс-накладную.
     *
     * @see https://developers.novaposhta.ua/view/model/a90d323c-8512-11ec-8ced-005056b2dbe1/method/a965630e-8512-11ec-8ced-005056b2dbe1 Создать экспресс-накладную
     *
     * @param  string|null  $description
     * @return array
     */
    public function save(string $description = null): array
    {
        $this->calledMethod = 'save';

        $this->getPayerType();
        $this->getServiceType();
        $this->getPaymentMethod();
        $this->getCargoType();

        $this->getDateTime();
        $this->setDescription($description);
        $this->getSeatsAmount();
        $this->getCost();
        $this->getOptionsSeat();

        //Отправитель и другое
        $this->getSender();
        $this->getRecipientType();
        $this->getBackwardDeliveryData();
        $this->getNote();
        $this->getAdditionalInformation();

        return $this->getResponse($this->model, $this->calledMethod, $this->methodProperties);
    }

    /**
     * Удаление экспресс-накладной.
     *
     * @see https://developers.novaposhta.ua/view/model/a90d323c-8512-11ec-8ced-005056b2dbe1/method/a9f43ff1-8512-11ec-8ced-005056b2dbe1 Удаление экспресс-накладной
     *
     * @param  string|array  $DocumentRefs
     * @return array
     */
    public function delete($DocumentRefs): array
    {
        $this->calledMethod = 'delete';

        if (is_array($DocumentRefs) === false) {
            $DocumentRefs = explode(', ', /** @scrutinizer ignore-type */ $DocumentRefs);
        }

        $this->methodProperties['DocumentRefs'] = array_values(/** @scrutinizer ignore-type */ $DocumentRefs);

        return $this->getResponse($this->model, $this->calledMethod, $this->methodProperties);
    }

    /**
     * Получить данные о платежах за определенный период.
     *
     * @deprecated НЕ ДОКУМЕНТИРОВАНО
     *
     * @param  null  $dateFrom
     * @param  null  $dateTo
     * @return array
     */
    public function getMoneyTransferDocuments($dateFrom = null, $dateTo = null): array
    {
        $this->calledMethod = 'getMoneyTransferDocuments';

        $this->addLimit();
        $this->getPage();
        $this->getDateFromTo($dateFrom, $dateTo);

        return $this->getResponse($this->model, $this->calledMethod, $this->methodProperties);
    }

    /**
     * Редактирование экспресс-накладной.
     *
     * @see https://developers.novaposhta.ua/view/model/a90d323c-8512-11ec-8ced-005056b2dbe1/method/a98a4354-8512-11ec-8ced-005056b2dbe1 Редактирование экспресс-накладной
     *
     * @author https://github.com/seriklav/NovaPoshta
     *
     * TODO need tested
     *
     * @deprecated НЕ ПРОВЕРЕНО
     *
     * @param  null  $description
     * @return array
     */
    public function edit($description = null): array
    {
        $this->calledMethod = 'update';

        $this->getRef();

        $this->getPayerType();
        $this->getServiceType();
        $this->getPaymentMethod();
        $this->getCargoType();

        $this->getDateTime();
        $this->setDescription($description);
        $this->getSeatsAmount();
        $this->getCost();
        $this->getWeight();
//        $this->getOptionsSeat();

        //Отправитель и другое
        $this->getSender();
        $this->getRecipientType();
        $this->getBackwardDeliveryData();
        $this->getNote();
        $this->getAdditionalInformation();

        return $this->getResponse($this->model, $this->calledMethod, $this->methodProperties);
    }

    /**
     * Прогноз даты доставки груза.
     *
     * @see https://developers.novaposhta.ua/view/model/a90d323c-8512-11ec-8ced-005056b2dbe1/method/a941c714-8512-11ec-8ced-005056b2dbe1 Прогноз даты доставки груза
     *
     * @author https://github.com/seriklav/NovaPoshta
     *
     * @param  string  $CitySender
     * @param  string  $CityRecipient
     * @param  string|null  $DateTime
     * @param  string|null  $ServiceType
     * @return array
     */
    public function getDocumentDeliveryDate(
        string $CitySender,
        string $CityRecipient,
        ?string $DateTime = null,
        ?string $ServiceType = null
    ): array {
        $this->calledMethod = 'getDocumentDeliveryDate';

        $methodProperties['ServiceType'] = $ServiceType ?? config('novaposhta.service_type');
        $methodProperties['CitySender'] = $CitySender;
        $methodProperties['CityRecipient'] = $CityRecipient;

        if ($DateTime) {
            $methodProperties['DateTime'] = $this->checkDate($DateTime, 'd.m.Y');
        }

        return $this->getResponse($this->model, $this->calledMethod, $methodProperties, false);
    }
}
