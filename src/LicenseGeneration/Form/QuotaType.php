<?php

namespace App\LicenseGeneration\Form;

use App\License\LicenseQuotaInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class QuotaType extends AbstractType
{
    /**
     * @var LicenseQuotaInterface[]
     */
    private iterable $licenseQuotas;

    public function __construct(iterable $licenseQuotas)
    {
        $this->licenseQuotas = $licenseQuotas;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        foreach ($this->licenseQuotas as $quota) {
            $quotaName = $quota->getName();

            $builder->add($quotaName, null, [
                'label' => "quota.$quotaName.name",
                'help' => "quota.$quotaName.description",
            ]);
        }
    }
}
