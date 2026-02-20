<?php

namespace ExprAs\Nutgram\Mezzio\Handler;

use Doctrine\ORM\EntityManager;
use ExprAs\Core\Handler\AbstractActionHandler;
use ExprAs\Core\Response\HttpCachedResponse;
use ExprAs\Core\ServiceManager\ServiceContainerAwareTrait;
use ExprAs\Doctrine\Hydrator\DoctrineEntity;
use ExprAs\Nutgram\Entity\MessageToUser;
use ExprAs\Nutgram\Entity\UserMessage;
use Laminas\Diactoros\Response\JsonResponse;
use Laminas\Hydrator\HydratorPluginManager;
use Mezzio\Authentication\UserInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Types\Internal\InputFile;

class ChatActions extends AbstractActionHandler
{
    use ServiceContainerAwareTrait;

    public function sendMessageAction(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        /** @var Nutgram $bot */
        $bot = $this->getContainer()->get(Nutgram::class);
        $user = $request->getAttribute(UserInterface::class);
        try {
            $userId = $this->params($request)->fromParsedBody('userId');
            $messageText = $this->params($request)->fromParsedBody('messageText');

            // Handle file upload
            $uploadedFiles = $request->getUploadedFiles();
            $attachmentObject = null;
            $response = null;

            if (isset($uploadedFiles['attachment']) && $uploadedFiles['attachment']->getSize() > 0) {
                $uploadedFile = $uploadedFiles['attachment'];
                $mimeType = $uploadedFile->getClientMediaType();

                // Create temporary file with proper extension
                $tempDir = sys_get_temp_dir();
                $tempFileName = 'nutgram_upload_' . uniqid() . '_' . $uploadedFile->getClientFilename();
                $tempPath = $tempDir . DIRECTORY_SEPARATOR . $tempFileName;

                $uploadedFile->moveTo($tempPath);

                try {
                    // Validate file exists and has content
                    if (!file_exists($tempPath) || filesize($tempPath) === 0) {
                        throw new \Exception('Uploaded file is empty or invalid');
                    }

                    if (str_starts_with($mimeType, 'image/') && !str_ends_with($mimeType, 'gif')) {
                        // For images, try sendPhoto first, fallback to document if it fails
                        try {
                            $response = $bot->sendPhoto(
                                photo: InputFile::make($tempPath),
                                chat_id: $userId,
                                caption: $messageText ?: null
                            );
                            $attachmentObject = [
                                'photo' => array_map(function ($photo) {
                                    return [
                                        'file_id' => $photo->file_id,
                                        'file_unique_id' => $photo->file_unique_id,
                                        'width' => $photo->width,
                                        'height' => $photo->height,
                                        'file_size' => $photo->file_size ?? null
                                    ];
                                }, $response->photo ?? []),
                                'caption' => $response->caption
                            ];
                        } catch (\Exception $photoException) {
                            // If sendPhoto fails, try as document
                            $response = $bot->sendDocument(
                                document: InputFile::make($tempPath),
                                chat_id: $userId,
                                caption: $messageText ?: null
                            );
                            $attachmentObject = [
                                'document' => [
                                    'file_id' => $response->document->file_id,
                                    'file_unique_id' => $response->document->file_unique_id,
                                    'file_name' => $response->document->file_name ?? $uploadedFile->getClientFilename(),
                                    'file_size' => $response->document->file_size ?? null,
                                    'mime_type' => $response->document->mime_type ?? $mimeType
                                ],
                                'caption' => $response->caption
                            ];
                        }
                    } elseif (str_starts_with($mimeType, 'video/')) {
                        $response = $bot->sendVideo(
                            video: InputFile::make($tempPath),
                            chat_id: $userId,
                            caption: $messageText ?: null
                        );
                        $attachmentObject = [
                            'video' => [
                                'file_id' => $response->video->file_id,
                                'file_unique_id' => $response->video->file_unique_id,
                                'width' => $response->video->width,
                                'height' => $response->video->height,
                                'duration' => $response->video->duration,
                                'file_size' => $response->video->file_size ?? null,
                                'mime_type' => $response->video->mime_type ?? $mimeType
                            ],
                            'caption' => $response->caption
                        ];
                    } elseif (str_starts_with($mimeType, 'audio/')) {
                        // Check if it's a voice message (short duration) or music file
                        $fileExtension = pathinfo($uploadedFile->getClientFilename(), PATHINFO_EXTENSION);
                        if (in_array(strtolower($fileExtension), ['ogg', 'oga']) || str_contains($mimeType, 'ogg')) {
                            $response = $bot->sendVoice(
                                voice: InputFile::make($tempPath),
                                chat_id: $userId,
                                caption: $messageText ?: null
                            );
                            $attachmentObject = [
                                'voice' => [
                                    'file_id' => $response->voice->file_id,
                                    'file_unique_id' => $response->voice->file_unique_id,
                                    'duration' => $response->voice->duration,
                                    'file_size' => $response->voice->file_size ?? null,
                                    'mime_type' => $response->voice->mime_type ?? $mimeType
                                ],
                                'caption' => $response->caption
                            ];
                        } else {
                            $response = $bot->sendAudio(
                                audio: InputFile::make($tempPath),
                                chat_id: $userId,
                                caption: $messageText ?: null
                            );
                            $attachmentObject = [
                                'audio' => [
                                    'file_id' => $response->audio->file_id,
                                    'file_unique_id' => $response->audio->file_unique_id,
                                    'duration' => $response->audio->duration,
                                    'file_name' => $response->audio->file_name ?? $uploadedFile->getClientFilename(),
                                    'file_size' => $response->audio->file_size ?? null,
                                    'mime_type' => $response->audio->mime_type ?? $mimeType
                                ],
                                'caption' => $response->caption
                            ];
                        }
                    } else {
                        $response = $bot->sendDocument(
                            document: InputFile::make($tempPath),
                            chat_id: $userId,
                            caption: $messageText ?: null
                        );
                        $attachmentObject = [
                            'document' => [
                                'file_id' => $response->document->file_id,
                                'file_unique_id' => $response->document->file_unique_id,
                                'file_name' => $response->document->file_name ?? $uploadedFile->getClientFilename(),
                                'file_size' => $response->document->file_size ?? null,
                                'mime_type' => $response->document->mime_type ?? $mimeType
                            ],
                            'caption' => $response->caption
                        ];
                    }
                } finally {
                    // Clean up temporary file
                    if (file_exists($tempPath)) {
                        unlink($tempPath);
                    }
                }
            } else {
                // No attachment, send text message only
                $response = $bot->sendMessage(
                    text: $messageText,
                    chat_id: $userId,
                );
            }

            /** @var EntityManager $em */
            $em = $this->getContainer()->get(EntityManager::class);
            /** @var DoctrineEntity $hydrator */
            $hydrator = $this->getContainer()->get(HydratorPluginManager::class)->get(DoctrineEntity::class);
            $messageToUser = $hydrator->hydrate([
                'text' => $messageText,
                'sender' => $user,
                'addressee' => $userId,
                'attachmentObject' => $attachmentObject
            ], new MessageToUser());

            $em->persist($messageToUser);
            $em->flush();

            return new JsonResponse([
                'success' => true
            ]);

        } catch (\Exception $e) {
            // Clean up temp file if it exists
            if (isset($tempPath) && file_exists($tempPath)) {
                unlink($tempPath);
            }
            return new JsonResponse(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function conversationAction(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        /** @var EntityManager $em */
        $em = $this->getContainer()->get(EntityManager::class);

        $userId = $this->params($request)->fromParsedBody('userId');
        $userMessages = $em->getRepository(UserMessage::class)->findBy(['user' => $userId], ['ctime' => 'desc'], 20);

        $messagesToUser = $em->getRepository(MessageToUser::class)->findBy(['addressee' => $userId], ['ctime' => 'desc'], 20);
        /** @var MessageToUser[] | UserMessage[] $messages */
        $messages = [...$messagesToUser, ...$userMessages];
        usort($messages, fn($a, $b) => $a->getCtime() <=> $b->getCtime());

        $messages = array_reverse($messages);

        return new JsonResponse(array_map(fn($message) => [
            'message' => $message instanceof MessageToUser ? $message->getText() : $message->getTextMessage(),
            'ctime' => $message->getCtime()->format('c'),
            'owner' => $message instanceof MessageToUser ? $message->getSender()->getUsername() : $message->getUser()->getUsername(),
            'avatar' => $message instanceof MessageToUser
                ? ($message->getSender()->getEmail() ? sprintf('https://www.gravatar.com/avatar/%s?s=32', md5($message->getSender()->getEmail())) : null)
                : '/avatar/' . $message->getUser()->getId(),
            'isModerator' => $message instanceof MessageToUser,
            'object' => $message instanceof MessageToUser ? $message->getAttachmentObject() : $message->getMessageObject()
        ], $messages));
    }

    public function fileAction(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (!($fileId = $this->params($request)->fromQuery('fileId', $this->params($request)->fromQuery('file_id')))) {
            return $handler->handle($request);
        }

        try {
            /** @var Nutgram $bot */
            $bot = $this->getContainer()->get(Nutgram::class);
            $file = $bot->getFile($fileId);
            //$path = $file->file_path;
            $fileIdMd5 = md5((string) $fileId);

            $filePath = 'data/files/' . substr($fileIdMd5, -2) . '/' . $fileIdMd5;

            if (!is_dir(dirname($filePath))) {
                mkdir(dirname($filePath), 0777, true);
            }

            if (!is_file($filePath)) {
                $bot->downloadFile($file, $filePath);
            }
        } catch (\Exception $e) {
            return new JsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }

        return new HttpCachedResponse($filePath, $request);
    }
}
