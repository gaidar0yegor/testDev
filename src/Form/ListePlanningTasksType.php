<?php

namespace App\Form;

use App\Entity\ProjetPlanning;
use App\Entity\ProjetPlanningTask;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ListePlanningTasksType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('projetPlanningTasks', CollectionType::class, [
                'entry_type' => ProjetPlanningTaskType::class,
                'entry_options' => array('projetPlanning' => $builder->getData()),
                'allow_add' => false,
                'allow_delete' => false,
                'label' => false,
                'required' => false,
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'update',
                'attr' => ['class' => 'btn btn-success mt-3 d-block'],
            ])
        ;
    }

    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        foreach ($view['projetPlanningTasks']->children as $childView)
        {
            /** @var ProjetPlanningTask $task */
            $task = $childView->vars['data'];

            $childView->vars['label'] = $task->getText();
            $childView->vars['sort'] = $task->getId();
            if ($task->getParentTask() !== null){
                $parentTask = $task->getParentTask();
                $childView->vars['sort'] = $parentTask->getId() . "." . $childView->vars['sort'];
                if ($parentTask->getParentTask() !== null){
                    $childView->vars['sort'] = $parentTask->getParentTask()->getId() . "." . $childView->vars['sort'];
                }
            }
        }

        usort($view['projetPlanningTasks']->children, function (FormView $a, FormView $b) {
            return 1 * version_compare ( $a->vars['sort'] , $b->vars['sort'] );
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ProjetPlanning::class,
        ]);
    }
}
