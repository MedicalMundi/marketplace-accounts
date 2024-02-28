<?php declare(strict_types=1);

/*
 * This file is part of the medicalmundi/marketplace-accounts
 *
 * @copyright (c) 2023 MedicalMundi
 *
 * This software consists of voluntary contributions made by many individuals
 * {@link https://github.com/medicalmundi/marketplace-accounts/graphs/contributors developer} and is licensed under the MIT license.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * @license https://github.com/MedicalMundi/marketplace-accounts/blob/main/LICENSE MIT
 */

namespace IdentityAccess\AdapterForWeb\Administration\OauthClient\Form;

use IdentityAccess\AdapterForWeb\Administration\OauthClient\Form\Dto\OauthClientDto;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/** @extends AbstractType<mixed> */
class OauthClientType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class)
            ->add('identifier', TextType::class)
            ->add('secret', TextType::class)
            ->add('redirectUris', CollectionType::class, [
                'entry_type' => TextType::class,
                'entry_options' => [
                    'label' => false,
                ],
                'allow_add' => true,
                'by_reference' => false,
            ])
            //TODO:
            //->add('grants', CollectionType::class, ['entry_type' => TextType::class])
            //->add('scopes', CollectionType::class, ['entry_type' => TextType::class])
            ->add('active', CheckboxType::class)
            ->add('allowPlainTextPkce', CheckboxType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => OauthClientDto::class,
        ]);
    }
}
