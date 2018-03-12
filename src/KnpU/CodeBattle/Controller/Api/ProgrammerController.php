<?php

namespace KnpU\CodeBattle\Controller\Api;

use KnpU\CodeBattle\Api\ApiProblem;
use KnpU\CodeBattle\Api\ApiProblemException;
use KnpU\CodeBattle\Controller\BaseController;
use KnpU\CodeBattle\Model\Programmer;
use Silex\Application;
use Silex\ControllerCollection;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ProgrammerController extends BaseController
{
    protected function addRoutes(ControllerCollection $controllers)
    {
        $controllers->post('/api/programmers', array($this, 'newAction'));
        $controllers->get("/api/programmers", array($this, "listAction"));
        $controllers->get("/api/programmers/{nickname}", array($this, "showAction"))
                    ->bind("api_programmers_show");
        $controllers->put("/api/programmers/{nickname}", array($this, "updateAction"));
        $controllers->match("/api/programmers/{nickname}", array($this, "updateAction"))
            ->method("PATCH");
        $controllers->delete("/api/programmers/{nickname}", array($this, "deleteAction"));
    }

    public function newAction(Request $request)
    {
        $programmer = new Programmer();

        $this->handleRequest($request, $programmer);

        $errors = $this->validate($programmer);
        if (!empty($errors)) {
            $this->throwApiProblemValidationException($errors);
        }

        $this->save($programmer);

        $url = $this->generateUrl("api_programmers_show", [
            "nickname" => $programmer->nickname,
        ]);

        $json = $this->serialize($programmer);

        $response = new Response($json, 201);
        $response->headers->set("Location", $url);

        return $response;
    }

    public function updateAction(Request $request, $nickname)
    {
//        throw new \Exception("You coded something wrong! FROM BRAZILLALALA");

        $programmer = $this->getProgrammerRepository()
            ->findOneByNickname($nickname);

        if(!$programmer) {
            // $this->throw404("Oh no! This programmer has deserted! We will send a search party");
            $programmer = new Programmer($nickname);
        }

        $this->handleRequest($request, $programmer);

        $errors = $this->validate($programmer);
        if (!empty($errors)) {
            $this->throwApiProblemValidationException($errors);
        }

        $this->save($programmer);
        $json = $this->serialize($programmer);

        $response = new Response($json, 200);

        return $response;
    }

    public function showAction($nickname)
    {
        $programmer = $this->getProgrammerRepository()
                ->findOneByNickname($nickname);

        if(!$programmer) {
            $this->throw404("Oh no! This programmer has deserted! We'll send a search party");
        }

        $json = $this->serialize($programmer);

        $response = new Response($json, 200);

        return $response;
    }

    public function deleteAction($nickname)
    {
        $programmer = $this->getProgrammerRepository()
            ->findOneByNickname($nickname);

        if ($programmer) {
            $this->delete($programmer);
        }

        return new Response(null, 204);
    }

    public function listAction()
    {
        $programmers = $this->getProgrammerRepository()
            ->findAll();

        $data = ["programmers" => $programmers];
        $json = $this->serialize($data);

        $response = new Response($json, 200);

        return $response;
    }

    private function serialize($data)
    {
        return $this->container['serializer']
                    ->serialize($data, 'json');
    }

    private function handleRequest(Request $request, Programmer $programmer)
    {
        $data = json_decode($request->getContent(), true);
        if ($data === null) {
            $apiProblem = new ApiProblem(400, ApiProblem::TYPE_INVALID_REQUEST_BODY_FORMAT);
            throw new ApiProblemException($apiProblem);
        }

        $isNew = !$programmer->id;

        $apiProperties = array("avatarNumber", "tagLine");
        if ($isNew) {
            $apiProperties[] = "nickname";
        }

        foreach ($apiProperties as $property) {
            // if PATCH and the field isn't send, just skip it!
            if ($request->isMethod("PATCH") && !isset($data[$property])) {
                continue;
            }

            $val = isset($data[$property]) ? $data[$property] : null;
            $programmer->$property = $val;
        }

        $programmer->userId = $this->findUserByUsername("weaverryan")->id;
    }

    private function throwApiProblemValidationException(array $errors)
    {

        $apiProblem = new ApiProblem(
            400,
            ApiProblem::TYPE_INVALID_REQUEST_BODY_FORMAT
        );
        
        $apiProblem->set('errors', $errors);

        throw new ApiProblemException($apiProblem);
    }
}
