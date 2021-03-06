<?php

final class PhabricatorUserDisableTransaction
  extends PhabricatorUserTransactionType {

  const TRANSACTIONTYPE = 'user.disable';

  public function generateOldValue($object) {
    return (bool)$object->getIsDisabled();
  }

  public function generateNewValue($object, $value) {
    return (bool)$value;
  }

  public function applyInternalEffects($object, $value) {
    $object->setIsDisabled((int)$value);

    $this->newUserLog(PhabricatorUserLog::ACTION_DISABLE)
      ->setOldValue((bool)$object->getIsDisabled())
      ->setNewValue((bool)$value)
      ->save();
  }

  public function getTitle() {
    $new = $this->getNewValue();
    if ($new) {
      return pht(
        '%s disabled this user.',
        $this->renderAuthor());
    } else {
      return pht(
        '%s enabled this user.',
        $this->renderAuthor());
    }
  }

  public function getTitleForFeed() {
    $new = $this->getNewValue();
    if ($new) {
      return pht(
        '%s disabled %s.',
        $this->renderAuthor(),
        $this->renderObject());
    } else {
      return pht(
        '%s enabled %s.',
        $this->renderAuthor(),
        $this->renderObject());
    }
  }

  public function validateTransactions($object, array $xactions) {
    $errors = array();

    foreach ($xactions as $xaction) {
      $is_disabled = (bool)$object->getIsDisabled();

      if ((bool)$xaction->getNewValue() === $is_disabled) {
        continue;
      }

      if ($this->getActingAsPHID() === $object->getPHID()) {
        $errors[] = $this->newInvalidError(
          pht('You can not enable or disable your own account.'));
      }
    }

    return $errors;
  }

}
