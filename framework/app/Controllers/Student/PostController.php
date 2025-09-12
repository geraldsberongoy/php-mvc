<?php
namespace App\Controllers\Student;

use App\Models\Classroom;
use App\Models\ClassroomPost;
use App\Models\PostComment;
use Gerald\Framework\Http\Response;

class PostController extends BaseStudentController
{
    /**
     * Show posts for a specific classroom
     */
    public function index(string $classroomId): Response
    {
        $classroomModel = new Classroom();
        $classroom      = $classroomModel->getWithTeacherDetails((int) $classroomId);

        if (! $classroom) {
            return Response::redirect('/student/classes?error=Classroom not found');
        }

        // Check if student is enrolled in this classroom
        $students   = $classroomModel->getEnrolledStudents((int) $classroomId);
        $isEnrolled = false;
        foreach ($students as $student) {
            if ($student['id'] == $this->userId) {
                $isEnrolled = true;
                break;
            }
        }

        if (! $isEnrolled) {
            return Response::redirect('/student/classes?error=Access denied');
        }

        $postModel = new ClassroomPost();

        // Get post type filter if provided
        $typeFilter = $this->request->getQuery('type');
        if ($typeFilter && in_array($typeFilter, array_keys(ClassroomPost::getPostTypes()))) {
            $posts = $postModel->getByClassroomAndType((int) $classroomId, $typeFilter);
        } else {
            $posts = $postModel->getByClassroom((int) $classroomId);
        }

        return $this->renderStudent('student/classrooms/posts/index.html.twig', [
            'classroom'           => $classroom,
            'posts'               => $posts,
            'post_types'          => ClassroomPost::getPostTypes(),
            'current_type_filter' => $typeFilter,
            'current_route'       => '/student/classes',
        ]);
    }

    /**
     * Show a specific post with comments
     */
    public function show(string $classroomId, string $postId): Response
    {
        $classroomModel = new Classroom();
        $classroom      = $classroomModel->getWithTeacherDetails((int) $classroomId);

        if (! $classroom) {
            return Response::redirect('/student/classes?error=Classroom not found');
        }

        // Check if student is enrolled in this classroom
        $students   = $classroomModel->getEnrolledStudents((int) $classroomId);
        $isEnrolled = false;
        foreach ($students as $student) {
            if ($student['id'] == $this->userId) {
                $isEnrolled = true;
                break;
            }
        }

        if (! $isEnrolled) {
            return Response::redirect('/student/classes?error=Access denied');
        }

        $postModel = new ClassroomPost();
        $post      = $postModel->findWithAuthor((int) $postId);

        if (! $post || $post['classroom_id'] != $classroomId) {
            return Response::redirect("/student/classes/{$classroomId}/posts?error=Post not found");
        }

        $commentModel = new PostComment();
        $comments     = $commentModel->getThreadedComments((int) $postId);

        return $this->renderStudent('student/classrooms/posts/show.html.twig', [
            'classroom'     => $classroom,
            'post'          => $post,
            'comments'      => $comments,
            'current_route' => '/student/classes',
        ]);
    }

    /**
     * Add a comment to a post
     */
    public function addComment(string $classroomId, string $postId): Response
    {
        $classroomModel = new Classroom();
        $classroom      = $classroomModel->find((int) $classroomId);

        if (! $classroom || ! is_array($classroom)) {
            return Response::redirect('/student/classes?error=Classroom not found');
        }

        // Check if student is enrolled in this classroom
        $students   = $classroomModel->getEnrolledStudents((int) $classroomId);
        $isEnrolled = false;
        foreach ($students as $student) {
            if ($student['id'] == $this->userId) {
                $isEnrolled = true;
                break;
            }
        }

        if (! $isEnrolled) {
            return Response::redirect('/student/classes?error=Access denied');
        }

        $content  = $this->request->getPost('content');
        $parentId = $this->request->getPost('parent_id');

        if (! $content) {
            return Response::redirect("/student/classes/{$classroomId}/posts/{$postId}?error=Comment content is required");
        }

        try {
            $commentModel = new PostComment();
            $commentModel->create([
                'post_id'   => (int) $postId,
                'author_id' => $this->userId,
                'content'   => $content,
                'parent_id' => $parentId ? (int) $parentId : null,
            ]);

            return Response::redirect("/student/classes/{$classroomId}/posts/{$postId}?success=Comment added successfully");
        } catch (\Exception $e) {
            return Response::redirect("/student/classes/{$classroomId}/posts/{$postId}?error=Error adding comment: " . $e->getMessage());
        }
    }

    /**
     * Edit a comment (if it belongs to the student)
     */
    public function editComment(string $classroomId, string $postId, string $commentId): Response
    {
        $classroomModel = new Classroom();
        $classroom      = $classroomModel->find((int) $classroomId);

        if (! $classroom || ! is_array($classroom)) {
            return Response::redirect('/student/classes?error=Classroom not found');
        }

        // Check if student is enrolled in this classroom
        $students   = $classroomModel->getEnrolledStudents((int) $classroomId);
        $isEnrolled = false;
        foreach ($students as $student) {
            if ($student['id'] == $this->userId) {
                $isEnrolled = true;
                break;
            }
        }

        if (! $isEnrolled) {
            return Response::redirect('/student/classes?error=Access denied');
        }

        $commentModel = new PostComment();
        $comment      = $commentModel->find((int) $commentId);

        if (! $comment || ! is_array($comment)) {
            return Response::redirect("/student/classes/{$classroomId}/posts/{$postId}?error=Comment not found");
        }

        // Check if this student owns the comment
        if ($comment['author_id'] != $this->userId) {
            return Response::redirect("/student/classes/{$classroomId}/posts/{$postId}?error=Access denied");
        }

        $content = $this->request->getPost('content');
        if (! $content) {
            return Response::redirect("/student/classes/{$classroomId}/posts/{$postId}?error=Comment content is required");
        }

        try {
            $commentModel->updateComment((int) $commentId, [
                'content' => $content,
            ]);

            return Response::redirect("/student/classes/{$classroomId}/posts/{$postId}?success=Comment updated successfully");
        } catch (\Exception $e) {
            return Response::redirect("/student/classes/{$classroomId}/posts/{$postId}?error=Error updating comment: " . $e->getMessage());
        }
    }

    /**
     * Delete a comment (if it belongs to the student)
     */
    public function deleteComment(string $classroomId, string $postId, string $commentId): Response
    {
        $classroomModel = new Classroom();
        $classroom      = $classroomModel->find((int) $classroomId);

        if (! $classroom || ! is_array($classroom)) {
            return Response::redirect('/student/classes?error=Classroom not found');
        }

        // Check if student is enrolled in this classroom
        $students   = $classroomModel->getEnrolledStudents((int) $classroomId);
        $isEnrolled = false;
        foreach ($students as $student) {
            if ($student['id'] == $this->userId) {
                $isEnrolled = true;
                break;
            }
        }

        if (! $isEnrolled) {
            return Response::redirect('/student/classes?error=Access denied');
        }

        $commentModel = new PostComment();
        $comment      = $commentModel->find((int) $commentId);

        if (! $comment || ! is_array($comment)) {
            return Response::redirect("/student/classes/{$classroomId}/posts/{$postId}?error=Comment not found");
        }

        // Check if this student owns the comment
        if ($comment['author_id'] != $this->userId) {
            return Response::redirect("/student/classes/{$classroomId}/posts/{$postId}?error=Access denied");
        }

        try {
            $commentModel->deleteComment((int) $commentId);
            return Response::redirect("/student/classes/{$classroomId}/posts/{$postId}?success=Comment deleted successfully");
        } catch (\Exception $e) {
            return Response::redirect("/student/classes/{$classroomId}/posts/{$postId}?error=Error deleting comment: " . $e->getMessage());
        }
    }

    /**
     * Search posts in classroom
     */
    public function search(string $classroomId): Response
    {
        $classroomModel = new Classroom();
        $classroom      = $classroomModel->getWithTeacherDetails((int) $classroomId);

        if (! $classroom) {
            return Response::redirect('/student/classes?error=Classroom not found');
        }

        // Check if student is enrolled in this classroom
        $students   = $classroomModel->getEnrolledStudents((int) $classroomId);
        $isEnrolled = false;
        foreach ($students as $student) {
            if ($student['id'] == $this->userId) {
                $isEnrolled = true;
                break;
            }
        }

        if (! $isEnrolled) {
            return Response::redirect('/student/classes?error=Access denied');
        }

        $query = $this->request->getQuery('q', '');
        $posts = [];

        if ($query) {
            $postModel = new ClassroomPost();
            $posts     = $postModel->searchInClassroom((int) $classroomId, $query);
        }

        return $this->renderStudent('student/classrooms/posts/search.html.twig', [
            'classroom'     => $classroom,
            'posts'         => $posts,
            'search_query'  => $query,
            'current_route' => '/student/classes',
        ]);
    }
}
