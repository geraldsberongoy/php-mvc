<?php

namespace App\Controllers;

use Gerald\Framework\Controllers\AbstractController;
use Gerald\Framework\Http\Response;

class ClassroomController extends AbstractController
{
    // Controller methods for managing classrooms will go here
    public function viewClassroom():Response
    {
        return $this->render('student/classes.html.twig');
    }
}