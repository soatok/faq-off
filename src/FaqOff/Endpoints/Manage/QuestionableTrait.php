<?php
declare(strict_types=1);
namespace Soatok\FaqOff\Endpoints\Manage;

use ParagonIE\Ionizer\InputFilterContainer;
use Psr\Http\Message\{
    RequestInterface,
    ResponseInterface
};
use Soatok\AnthroKit\Endpoint;
use Soatok\FaqOff\Filter\ArchiveQuestionFilter;
use Soatok\FaqOff\Splices\Questions;

/**
 * Trait QuestionableTrait
 * @package Soatok\FaqOff\Endpoints\Manage
 *
 * @const string QUESTION_TYPE
 * @method array post(RequestInterface $req, $type = Endpoint::TYPE_FORM, ?InputFilterContainer $filter = null)
 * @property Questions $questions
 */
trait QuestionableTrait
{
    /**
     * Precondition: The user has already been authorized for the selected
     * {author, collection, entry} (select appropriate).
     *
     * This grabs the questions for a given {type}, then only archives it if
     * it's in the list of available questions. This will prevent a legitimate
     * user from archiving questions they don't have access to.
     *
     * @param RequestInterface $request
     * @param int $id
     * @param array $rp (Router Parameters)
     * @return ResponseInterface
     */
    protected function questionQueue(
        RequestInterface $request,
        int $id,
        array $rp = []
    ): ResponseInterface {
        $filter = new ArchiveQuestionFilter();
        $post = $this->post($request, Endpoint::TYPE_FORM, $filter);

        switch (static::QUESTION_TYPE) {
            case 'author':
                $questions = $this->questions->getForAuthor($id);
                $this->setTwigVar(
                    'return_uri',
                    "/manage/author/{$id}"
                );
                break;
            case 'collection':
                $questions = $this->questions->getForCollection($id);
                $this->setTwigVar(
                    'return_uri',
                    "/manage/collection/{$id}"
                );
                break;
            case 'entry':
                $questions = $this->questions->getForEntry($id);
                $this->setTwigVar(
                    'return_uri',
                    "/manage/collection/{$rp['collection']}/entry/{$rp['entry']}"
                );
                break;
            default:
                return $this->redirect('/manage');
        }

        if (!empty($post['question_id'])) {
            foreach ($questions as $question) {
                if ((int) $question['questionid'] === (int) $post['question_id']) {
                    if ($this->questions->archive((int) $post['question_id'])) {
                        return $this->redirect($request->getUri()->getPath());
                    } else {
                        $this->messageOnce('An error occurred archiving this question.', 'error');
                    }
                }
            }
            $this->messageOnce('Invalid question ID.', 'error');
        }

        return $this->view('manage/questions.twig', [
            'questions' => $questions
        ]);
    }
}
