<?php

namespace KnpU\CodeBattle\Controller\Api;

use KnpU\CodeBattle\Controller\BaseController;
use KnpU\CodeBattle\Model\Programmer;
use Silex\Application;
use Silex\ControllerCollection;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

class ProgrammerController extends BaseController
{
    protected function addRoutes(ControllerCollection $controllers)
    {
        $controllers->post('/api/programmers', array($this, 'newAction'));
        $controllers->get("/api/programmers", array($this, "listAction"));
        $controllers->get("/api/programmers/{nickname}", array($this, "showAction"))
                    ->bind("api_programmers_show");
        $controllers->put("/api/programmers/{nickname}", array($this, "updateAction"));
    }

    public function newAction(Request $request)
    {
        $programmer = new Programmer();

        $this->handleRequest($request, $programmer);

        $url = $this->generateUrl("api_programmers_show", [
            "nickname" => $programmer->nickname,
        ]);

        $data = $this->serializeProgrammer($programmer);

        $response = new JsonResponse($data, 201);
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

        $data = $this->serializeProgrammer($programmer);

        $response = new JsonResponse($data, 200);

        return $response;
    }

    public function showAction($nickname)
    {
        $programmer = $this->getProgrammerRepository()
                ->findOneByNickname($nickname);

        if(!$programmer) {
            $this->throw404("Oh no! This programmer has deserted! We will send a search party");
        }

        $data = $this->serializeProgrammer($programmer);

        $response = new JsonResponse($data, 200);

        return $response;
    }

    public function listAction()
    {
        $programmers = $this->getProgrammerRepository()
            ->findAll();

        $data = ["programmers" => []];

        foreach ($programmers as $programmer) {
            $data["programmers"][] = $this->serializeProgrammer($programmer);
        }

        $response = new JsonResponse($data, 200);

        return $response;
    }

    private function serializeProgrammer(Programmer $programmer)
    {
        return [
            "nickname"       => $programmer->nickname,
            "avatarNumber"   => $programmer->avatarNumber,
            "powerLevel"     => $programmer->powerLevel,
            "tagLine"        => $programmer->tagLine,
        ];
    }

    private function handleRequest(Request $request, Programmer $programmer)
    {
        $data = json_decode($request->getContent(), true);
        if ($data === null) {
            throw new \Exception("Invalid JSON!!!! " . $request->getContent());
        }

        $isNew = !$programmer->id;

        $apiProperties = array("avatarNumber", "tagLine");
        if ($isNew) {
            $apiProperties[] = "nickname";
        }

        foreach ($apiProperties as $property) {
            $val = isset($data[$property]) ? $data[$property] : null;
            $programmer->$property = $val;
        }

        $programmer->userId = $this->findUserByUsername("weaverryan")->id;

        $this->save($programmer);
    }

}
