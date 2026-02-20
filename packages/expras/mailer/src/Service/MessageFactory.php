<?php
/**
 * Author: Stanislav Anisimov <stanislav@ww9.ru>
 * Date: 15.10.13
 * Time: 17:01
 */

namespace ExprAs\Mailer\Service;

use ExprAs\Core\ServiceManager\ServiceContainerAwareTrait;
use ExprAs\Dom\Stdlib\Element as DomElement;
use ExprAs\Dom\Stdlib\Document as DomDocument;
use Laminas\Diactoros\ServerRequest;
use Laminas\Diactoros\UploadedFile;
use Laminas\Http\Client;
use Laminas\Stdlib\ArrayUtils;
use Mezzio\Application;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Part\DataPart;
use Symfony\Component\Mime\Part\File;
use Mezzio\Template\TemplateRendererInterface;

class MessageFactory
{

    use ServiceContainerAwareTrait;

    protected $defaultValues = array();


    /**
     *
     * @var TemplateRendererInterface
     */
    protected $renderer;

    public static function attrMatch($pattern, $node)
    {
        $textContent = implode(
            ' ',
            array_map(
                function ($node) {
                    return $node->value;
                },
                $node
            )
        );
        return preg_match($pattern, $textContent);
    }


    public function __construct($defaultValues = array())
    {
        $this->defaultValues = $defaultValues;
    }

    public function setMessageDefaults($defaultValues)
    {
        $this->defaultValues = ArrayUtils::merge($this->defaultValues, $defaultValues);
    }

    /**
     * @param \Laminas\View\Renderer\RendererInterface $renderer
     */
    public function setRenderer($renderer)
    {
        $this->renderer = $renderer;
    }

    /**
     * @return \Laminas\View\Renderer\RendererInterface
     */
    public function getRenderer()
    {
        if (!$this->renderer) {
            $this->renderer = $this->getContainer()->get(TemplateRendererInterface::class);
        }
        return $this->renderer;
    }

    /**
     * Create a DataPart from view model HTML
     * @param string|\Laminas\View\Model\ModelInterface $nameOrModel
     * @param array $variables
     * @return string
     */
    public function createHtmlFromViewModel($nameOrModel, $variables = []): string
    {
        $renderer = $this->getRenderer();
        return $renderer->render($nameOrModel, $variables);
    }


    /**
     * Create a DataPart attachment from uploaded file
     *
     * @param UploadedFile $upload
     * @param string|null $filename
     * @return DataPart
     */
    public function createAttachmentFromUpload(UploadedFile $upload, ?string $filename = null): DataPart
    {
        return new DataPart(
            $upload->getStream()->getContents(),
            $filename ?? $upload->getClientFilename(),
            $upload->getClientMediaType()
        );
    }

    /**
     * Create an Email message
     *
     * @param string| array $recipient,
     * @param string|null $html HTML content
     * @param string|null $text Plain text content
     * @param string|null $subject Subject line
     * @param array|string|null $from From address
     * @return Email
     */
    public function createMessage(
        array|string $recipient,
        ?string $html = null, 
        ?string $text = null, 
        ?string $subject = null, 
        
        array|string|null $from = null
        ): Email
    {
        if (is_string($from)) {
            $from = ['email' => $from];
        } elseif (empty($from)) {
            $from = $this->defaultValues['from'] ?? [];
        }



        $message = new Email();

        if (is_array($recipient)) {
            if (!empty($recipient['email'])) {
                $message->to(new Address($recipient['email'], $recipient['name'] ?? ''));
            } else {
                foreach ($recipient as $email => $name) {
                    $message->to(new Address($email, $name));
                }
            }
        } else {
            $message->to(new Address($recipient));
        }
        
        if (!empty($from['email'])) {
            $fromAddress = isset($from['name']) 
                ? new Address($from['email'], $from['name'])
                : new Address($from['email']);
            $message->from($fromAddress);
        }

        if ($subject) {
            $message->subject($subject);
        }

        if ($html) {
            $message->html($html);
        }

        if ($text) {
            $message->text($text);
        }

      

        return $message;
    }

    /**
     * Create a simple text Email
     *
     * @param string $text Text content
     * @param string|null $subject Subject line
     * @param array|string|null $from From address
     * @return Email
     */
    public function createTextMessage(string $text, ?string $subject = null, array|string|null $from = null): Email
    {
        return $this->createMessage(null, $text, $subject, $from);
    }

    /**
     * Create an HTML Email
     *
     * @param string $html HTML content
     * @param string|null $subject Subject line
     * @param array|string|null $from From address
     * @return Email
     */
    public function createHtmlMessage(string $html, ?string $subject = null, array|string|null $from = null): Email
    {
        return $this->createMessage($html, null, $subject, $from);
    }

    /**
     * Process HTML content and optionally embed images
     *
     * @param string $htmlContent
     * @param Email $email
     * @param bool $embedImages
     * @return Email
     */
    public function processHtmlWithImages(string $htmlContent, Email $email, bool $embedImages = false): Email
    {
        if (!$embedImages) {
            $email->html($htmlContent);
            return $email;
        }

        $wrapper = DomDocument::createElementFromHTML('<wrapper>' . $htmlContent . '</wrapper>');
        /** @var ServerRequest $request */
        $request = $this->getContainer()->get(ServerRequestInterface::class)();

        /** @var $_element DomElement */
        foreach ($wrapper->queryCss('* img') as $_element) {
            $src = $_element->getAttribute('src');
            
            // Get content from relative or absolute path
            if (substr($src, 0, 4) !== 'http') {
                $fullPath = 'public/' . ltrim($src, '/');
                if (file_exists($fullPath)) {
                    $_content = file_get_contents($fullPath);
                    $cid = md5($_content);
                    $email->embed($_content, $cid, mime_content_type($fullPath));
                    $_element->setAttribute('src', 'cid:' . $cid);
                }
            } else {
                $client = new Client($src);
                $_content = $client->send()->getBody();
                if ($_content) {
                    $cid = md5($_content);
                    $email->embed($_content, $cid);
                    $_element->setAttribute('src', 'cid:' . $cid);
                }
            }
        }

        $email->html($wrapper->innerHTML());
        return $email;
    }
}

