<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Taxes\Calculator;

class TestController
{
    /**
     * @Route("/", name="index")
     */
    public function index()
    {
        dd("Ca fonctionne");
    }

    /**
     *  @Route("/test/{age<\d+>?0}", name="test", methods={"GET", "POST"}, schemes={"http", "https"})
     */
    public function test(Request $request, $age, Calculator $calculator)
    {
        $tva = $calculator->calcul(200);
        dump($tva);

        return new Response("J'ai $age ans");
    }
}
