<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use App\Entity\User;
use App\Enums\User\JobPosition;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;

/**
 * @see https://symfony.com/doc/current/security/custom_authenticator.html
 */
class OAuthAuthenticator extends AbstractAuthenticator
{
    private const EMAIL = 'X-Forwarded-Email';

    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly string $appEnv,
        private readonly ?string $oauthFakeEmail = null,
    ) {}
    /**
     * Called on every request to decide if this authenticator should be
     * used for the request. Returning `false` will cause this authenticator
     * to be skipped.
     */
    public function supports(Request $request): ?bool
    {
        if ($request->headers->has(self::EMAIL)) {
            return true;
        }

        return 'dev' === $this->appEnv && null !== $this->oauthFakeEmail;
    }

    public function authenticate(Request $request): Passport
    {
        $email = $request->headers->get(self::EMAIL);

        if (null === $email && 'dev' === $this->appEnv) {
            $email = $this->oauthFakeEmail;
        }

        if (null === $email) {
            throw new CustomUserMessageAuthenticationException('No Email Provided');
        }

        $user = $this->userRepository->findOneBy(['email' => $email]);

        if (!$user) {
            $name = $request->headers->get('X-Forwarded-Name')
                ?? ucwords(str_replace('.', ' ', explode('@', $email)[0]));

            $user = (new User())
                ->setEmail($email)
                ->setName($name)
                ->setJobPosition(JobPosition::Fullstack)
                ->setCreatedAt(new \DateTime());

            try {
                $this->entityManager->persist($user);
                $this->entityManager->flush();
            } catch (\Doctrine\DBAL\Exception\UniqueConstraintViolationException) {
                $this->entityManager->clear();
                $user = $this->userRepository->findOneBy(['email' => $email]);
            }
        }

        // implement your own logic to get the user identifier from `$apiToken`
        // e.g. by looking up a user in the database using its API key
        // $userIdentifier = /** ... */;

        return new SelfValidatingPassport(new UserBadge($user->getEmail()));
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        // on success, let the request continue
        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        $data = [
            // you may want to customize or obfuscate the message first
            'message' => strtr($exception->getMessageKey(), $exception->getMessageData()),

            // or to translate this message
            // $this->translator->trans($exception->getMessageKey(), $exception->getMessageData())
        ];

        return new JsonResponse($data, Response::HTTP_UNAUTHORIZED);
    }

    // public function start(Request $request, ?AuthenticationException $authException = null): Response
    // {
    //     /*
    //      * If you would like this class to control what happens when an anonymous user accesses a
    //      * protected page (e.g. redirect to /login), uncomment this method and make this class
    //      * implement Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface.
    //      *
    //      * For more details, see https://symfony.com/doc/current/security/experimental_authenticators.html#configuring-the-authentication-entry-point
    //      */
    // }
}
