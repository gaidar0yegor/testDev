<?php

namespace App\Controller\CorpApp\API;

use App\Entity\FaitMarquant;
use App\Entity\FaitMarquantComment;
use App\Entity\ProjetObservateurExterne;
use App\File\FileHandler\AvatarHandler;
use App\MultiSociete\UserContext;
use App\Twig\DiffDateTimesExtension;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @Route("/api/fm-commentaire/{fmId}")
 */
class FaitMarquantCommentController extends AbstractController
{
    private UserContext $userContext;
    private EntityManagerInterface $em;
    private NormalizerInterface $normalizer;
    private AvatarHandler $avatarHandler;
    private DiffDateTimesExtension $diffDateTimesExtension;

    public function __construct(
        UserContext $userContext,
        EntityManagerInterface $em,
        NormalizerInterface $normalizer,
        AvatarHandler $avatarHandler,
        DiffDateTimesExtension $diffDateTimesExtension
    )
    {
        $this->userContext = $userContext;
        $this->em = $em;
        $this->normalizer = $normalizer;
        $this->avatarHandler = $avatarHandler;
        $this->diffDateTimesExtension = $diffDateTimesExtension;
    }

    /**
     * @Route(
     *      "/ajouter",
     *      methods={"POST"},
     *      name="api_fm_comment_new"
     * )
     *
     * @ParamConverter("faitMarquant", options={"id" = "fmId"})
     */
    public function new(FaitMarquant $faitMarquant, Request $request)
    {
        $text = $request->request->get('text');

        $comment = new FaitMarquantComment();
        $comment->setText($text);
        $comment->setFaitMarquant($faitMarquant);

        if ($this->userContext->hasSocieteUser()){
            $comment->setSocieteUser($this->userContext->getSocieteUser());
        } else {
            $comment->setObservateurExterne($this->em->getRepository(ProjetObservateurExterne::class)->findOneBy([
                'projet' => $faitMarquant->getProjet(),
                'user' => $this->getUser()
            ]));
        }

        $this->em->persist($comment);
        $this->em->flush();

        $data = $this->normalizer->normalize($comment,'json', [
            'groups' => 'comment'
        ]);

        $data['createdAt'] = $this->diffDateTimesExtension->diffDateTimes($comment->getCreatedAt());
        $data['role'] = $comment->getCreatedByRole();

        return new JsonResponse([
            "data" => $data,
            "avatarPublicUrl" => $this->avatarHandler->getPublicUrl(),
        ]);
    }

    /**
     * @Route(
     *      "/supprimer/{commentId}",
     *      methods={"DELETE"},
     *      name="api_fm_comment_delete"
     * )
     *
     * @ParamConverter("faitMarquant", options={"id" = "fmId"})
     * @ParamConverter("faitMarquantComment", options={"id" = "commentId"})
     */
    public function delete(FaitMarquant $faitMarquant, FaitMarquantComment $faitMarquantComment)
    {
        $this->em->remove($faitMarquantComment);
        $this->em->flush();

        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
    }
}
