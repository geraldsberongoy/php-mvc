<?php
namespace App\Controllers\Teacher;

use App\Models\Classroom;
use App\Models\ClassroomPost;
use App\Models\PostComment;
use Gerald\Framework\Http\Response;

class PostController extends BaseTeacherController
{
    /**
     * Show posts for a specific classroom
     */
    public function index(string $classroomId): Response
    {
        $classroomModel = new Classroom();
        $classroom      = $classroomModel->find((int) $classroomId);

        if (! $classroom || ! is_array($classroom)) {
            return Response::redirect('/teacher/classrooms?error=Classroom not found');
        }

        // Check if this teacher owns this classroom
        if ($classroom['teacher_id'] != $this->userId) {
            return Response::redirect('/teacher/classrooms?error=Access denied');
        }

        $postModel = new ClassroomPost();
        $posts     = $postModel->getByClassroom((int) $classroomId);

        // Get post type filter if provided
        $typeFilter = $this->request->getQuery('type');
        if ($typeFilter && in_array($typeFilter, array_keys(ClassroomPost::getPostTypes()))) {
            $posts = $postModel->getByType((int) $classroomId, $typeFilter);
        }

        return $this->renderTeacher('teacher/classrooms/show.html.twig', [
            'classroom'           => $classroom,
            'posts'               => $posts,
            'post_types'          => ClassroomPost::getPostTypes(),
            'current_type_filter' => $typeFilter,
            'current_route'       => '/teacher/classrooms',
        ]);
    }

    /**
     * Show create post form
     */
    public function create(string $classroomId): Response
    {
        $classroomModel = new Classroom();
        $classroom      = $classroomModel->find((int) $classroomId);

        if (! $classroom || ! is_array($classroom)) {
            return Response::redirect('/teacher/classrooms?error=Classroom not found');
        }

        // Check if this teacher owns this classroom
        if ($classroom['teacher_id'] != $this->userId) {
            return Response::redirect('/teacher/classrooms?error=Access denied');
        }

        return $this->renderTeacher('teacher/classrooms/posts/create.html.twig', [
            'classroom'     => $classroom,
            'post_types'    => ClassroomPost::getPostTypes(),
            'current_route' => '/teacher/classrooms',
        ]);
    }

    /**
     * Store a new post
     */
    public function store(string $classroomId): Response
    {
        $classroomModel = new Classroom();
        $classroom      = $classroomModel->find((int) $classroomId);

        if (! $classroom || ! is_array($classroom)) {
            return Response::redirect('/teacher/classrooms?error=Classroom not found');
        }

        // Check if this teacher owns this classroom
        if ($classroom['teacher_id'] != $this->userId) {
            return Response::redirect('/teacher/classrooms?error=Access denied');
        }

        $content  = $this->request->getPost('content');
        $postType = $this->request->getPost('post_type');
        $isPinned = $this->request->getPost('is_pinned', false);

        if (! $content) {
            // Check if this is from the stream tab
            if ($this->request->getPost('from_stream')) {
                return Response::redirect("/teacher/classrooms/{$classroomId}?error=Content is required");
            }

            return $this->renderTeacher('teacher/classrooms/posts/create.html.twig', [
                'classroom'     => $classroom,
                'post_types'    => ClassroomPost::getPostTypes(),
                'error'         => 'Content is required',
                'current_route' => '/teacher/classrooms',
            ]);
        }

        try {
            $postModel = new ClassroomPost();
            $postId    = $postModel->create([
                'classroom_id' => (int) $classroomId,
                'author_id'    => $this->userId,
                'content'      => $content,
                'post_type'    => $postType ?: ClassroomPost::TYPE_ANNOUNCEMENT,
                'is_pinned'    => (bool) $isPinned,
            ]);

            // If posted from stream tab, redirect back to classroom with stream tab
            if ($this->request->getPost('from_stream')) {
                return Response::redirect("/teacher/classrooms/{$classroomId}?success=Post created successfully#stream");
            }

            return Response::redirect("/teacher/classrooms/{$classroomId}/posts?success=Post created successfully");
        } catch (\Exception $e) {
            // If posted from stream tab, redirect back to classroom
            if ($this->request->getPost('from_stream')) {
                return Response::redirect("/teacher/classrooms/{$classroomId}?error=Error creating post: " . $e->getMessage());
            }

            return $this->renderTeacher('teacher/classrooms/posts/create.html.twig', [
                'classroom'     => $classroom,
                'post_types'    => ClassroomPost::getPostTypes(),
                'error'         => 'Error creating post: ' . $e->getMessage(),
                'current_route' => '/teacher/classrooms',
            ]);
        }
    }

    /**
     * Show a specific post with comments
     */
    public function show(string $classroomId, string $postId): Response
    {
        $classroomModel = new Classroom();
        $classroom      = $classroomModel->find((int) $classroomId);

        if (! $classroom || ! is_array($classroom)) {
            return Response::redirect('/teacher/classrooms?error=Classroom not found');
        }

        // Check if this teacher owns this classroom
        if ($classroom['teacher_id'] != $this->userId) {
            return Response::redirect('/teacher/classrooms?error=Access denied');
        }

        $postModel = new ClassroomPost();
        $post      = $postModel->findWithAuthor((int) $postId);

        if (! $post || $post['classroom_id'] != $classroomId) {
            return Response::redirect("/teacher/classrooms/{$classroomId}/posts?error=Post not found");
        }

        $commentModel = new PostComment();
        $comments     = $commentModel->getThreadedComments((int) $postId);

        return $this->renderTeacher('teacher/classrooms/posts/show.html.twig', [
            'classroom'     => $classroom,
            'post'          => $post,
            'comments'      => $comments,
            'current_route' => '/teacher/classrooms',
        ]);
    }

    /**
     * Show edit post form
     */
    public function edit(string $classroomId, string $postId): Response
    {
        $classroomModel = new Classroom();
        $classroom      = $classroomModel->find((int) $classroomId);

        if (! $classroom || ! is_array($classroom)) {
            return Response::redirect('/teacher/classrooms?error=Classroom not found');
        }

        // Check if this teacher owns this classroom
        if ($classroom['teacher_id'] != $this->userId) {
            return Response::redirect('/teacher/classrooms?error=Access denied');
        }

        $postModel = new ClassroomPost();
        $post      = $postModel->findWithAuthor((int) $postId);

        if (! $post || $post['classroom_id'] != $classroomId) {
            return Response::redirect("/teacher/classrooms/{$classroomId}/posts?error=Post not found");
        }

        // Check if user can modify this post
        if (! $postModel->canUserModifyPost((int) $postId, $this->userId)) {
            return Response::redirect("/teacher/classrooms/{$classroomId}/posts?error=Access denied");
        }

        return $this->renderTeacher('teacher/classrooms/posts/edit.html.twig', [
            'classroom'     => $classroom,
            'post'          => $post,
            'post_types'    => ClassroomPost::getPostTypes(),
            'current_route' => '/teacher/classrooms',
        ]);
    }

    /**
     * Update a post
     */
    public function update(string $classroomId, string $postId): Response
    {
        $classroomModel = new Classroom();
        $classroom      = $classroomModel->find((int) $classroomId);

        if (! $classroom || ! is_array($classroom)) {
            return Response::redirect('/teacher/classrooms?error=Classroom not found');
        }

        // Check if this teacher owns this classroom
        if ($classroom['teacher_id'] != $this->userId) {
            return Response::redirect('/teacher/classrooms?error=Access denied');
        }

        $postModel = new ClassroomPost();
        $post      = $postModel->findWithAuthor((int) $postId);

        if (! $post || $post['classroom_id'] != $classroomId) {
            return Response::redirect("/teacher/classrooms/{$classroomId}/posts?error=Post not found");
        }

        // Check if user can modify this post
        if (! $postModel->canUserModifyPost((int) $postId, $this->userId)) {
            return Response::redirect("/teacher/classrooms/{$classroomId}/posts?error=Access denied");
        }

        $content  = $this->request->getPost('content');
        $postType = $this->request->getPost('post_type');
        $isPinned = $this->request->getPost('is_pinned', false);

        if (! $content) {
            return $this->renderTeacher('teacher/classrooms/posts/edit.html.twig', [
                'classroom'     => $classroom,
                'post'          => $post,
                'post_types'    => ClassroomPost::getPostTypes(),
                'error'         => 'Content is required',
                'current_route' => '/teacher/classrooms',
            ]);
        }

        try {
            $postModel->updatePost((int) $postId, [
                'content'   => $content,
                'post_type' => $postType,
                'is_pinned' => (bool) $isPinned,
            ]);

            return Response::redirect("/teacher/classrooms/{$classroomId}/posts/{$postId}?success=Post updated successfully");
        } catch (\Exception $e) {
            return $this->renderTeacher('teacher/classrooms/posts/edit.html.twig', [
                'classroom'     => $classroom,
                'post'          => $post,
                'post_types'    => ClassroomPost::getPostTypes(),
                'error'         => 'Error updating post: ' . $e->getMessage(),
                'current_route' => '/teacher/classrooms',
            ]);
        }
    }

    /**
     * Delete a post
     */
    public function delete(string $classroomId, string $postId): Response
    {
        $classroomModel = new Classroom();
        $classroom      = $classroomModel->find((int) $classroomId);

        if (! $classroom || ! is_array($classroom)) {
            return Response::redirect('/teacher/classrooms?error=Classroom not found');
        }

        // Check if this teacher owns this classroom
        if ($classroom['teacher_id'] != $this->userId) {
            return Response::redirect('/teacher/classrooms?error=Access denied');
        }

        $postModel = new ClassroomPost();
        $post      = $postModel->findWithAuthor((int) $postId);

        if (! $post || $post['classroom_id'] != $classroomId) {
            return Response::redirect("/teacher/classrooms/{$classroomId}/posts?error=Post not found");
        }

        // Check if user can modify this post
        if (! $postModel->canUserModifyPost((int) $postId, $this->userId)) {
            return Response::redirect("/teacher/classrooms/{$classroomId}/posts?error=Access denied");
        }

        try {
            $postModel->deletePost((int) $postId);
            return Response::redirect("/teacher/classrooms/{$classroomId}/posts?success=Post deleted successfully");
        } catch (\Exception $e) {
            return Response::redirect("/teacher/classrooms/{$classroomId}/posts?error=Error deleting post: " . $e->getMessage());
        }
    }

    /**
     * Add a comment to a post
     */
    public function addComment(string $classroomId, string $postId): Response
    {
        $classroomModel = new Classroom();
        $classroom      = $classroomModel->find((int) $classroomId);

        if (! $classroom || ! is_array($classroom)) {
            return Response::redirect('/teacher/classrooms?error=Classroom not found');
        }

        // Check if this teacher owns this classroom
        if ($classroom['teacher_id'] != $this->userId) {
            return Response::redirect('/teacher/classrooms?error=Access denied');
        }

        $content  = $this->request->getPost('content');
        $parentId = $this->request->getPost('parent_id');

        if (! $content) {
            return Response::redirect("/teacher/classrooms/{$classroomId}/posts/{$postId}?error=Comment content is required");
        }

        try {
            $commentModel = new PostComment();
            $commentModel->create([
                'post_id'   => (int) $postId,
                'author_id' => $this->userId,
                'content'   => $content,
                'parent_id' => $parentId ? (int) $parentId : null,
            ]);

            return Response::redirect("/teacher/classrooms/{$classroomId}/posts/{$postId}?success=Comment added successfully");
        } catch (\Exception $e) {
            return Response::redirect("/teacher/classrooms/{$classroomId}/posts/{$postId}?error=Error adding comment: " . $e->getMessage());
        }
    }

    /**
     * Toggle pin status of a post
     */
    public function togglePin(string $classroomId, string $postId): Response
    {
        $classroomModel = new Classroom();
        $classroom      = $classroomModel->find((int) $classroomId);

        if (! $classroom || ! is_array($classroom)) {
            return Response::redirect('/teacher/classrooms?error=Classroom not found');
        }

        // Check if this teacher owns this classroom
        if ($classroom['teacher_id'] != $this->userId) {
            return Response::redirect('/teacher/classrooms?error=Access denied');
        }

        $postModel = new ClassroomPost();
        $post      = $postModel->find((int) $postId);

        if (! $post || ! is_array($post) || $post['classroom_id'] != $classroomId) {
            return Response::redirect("/teacher/classrooms/{$classroomId}/posts?error=Post not found");
        }

        try {
            $postModel->togglePin((int) $postId);
            $action = $post['is_pinned'] ? 'unpinned' : 'pinned';
            return Response::redirect("/teacher/classrooms/{$classroomId}/posts?success=Post {$action} successfully");
        } catch (\Exception $e) {
            return Response::redirect("/teacher/classrooms/{$classroomId}/posts?error=Error updating post: " . $e->getMessage());
        }
    }
}
