<?php
/**
 * @group ActivityStream
 */
class CommentsTest extends Sugar_PHPUnit_Framework_TestCase
{
    private $activity;

    public function setUp()
    {
        parent::setUp();
        SugarTestHelper::setUp('current_user');
        $this->activity = SugarTestActivityUtilities::createActivity();
    }

    public function tearDown()
    {
        SugarTestActivityUtilities::removeAllCreatedActivities();
        SugarTestCommentUtilities::removeAllCreatedComments();
        SugarTestHelper::tearDown();
        parent::tearDown();
    }

    /**
     * Tests that the magic method __toString() on a Comment bean is valid.
     * @covers Comment::toJson
     */
    public function testToString()
    {
        $comment = SugarTestCommentUtilities::createComment($this->activity);
        $json = $comment->toJson();
        $this->assertInternalType('string', $json);
        $this->assertNotEquals(false, json_decode($json, true));
    }

    /**
     * Tests that saving a comment that the post has already counted does not
     * increment the cached count again.
     * @covers Comment::save
     */
    public function testDoubleSaveDoesntUpdateCommentCount()
    {
        $comment = SugarTestCommentUtilities::createComment($this->activity);
        $this->assertEquals(1, $this->activity->comment_count);
        $comment->save();
        $this->assertEquals(1, $this->activity->comment_count);
    }

    /**
     * Tests that saving a comment without a parent post returns false.
     * @covers Comment::save
     */
    public function testSave_WithoutParentPost_ReturnsFalse()
    {
        $comment = BeanFactory::getBean('Comments');
        $id = $comment->save();
        $this->assertFalse($id);
    }

    /**
     * @covers Comment::processCommentTags
     */
    public function testProcessCommentTags_NoTagsOnComment_ProcessTagsNotCalled()
    {
        $comment = BeanFactory::getBean('Comments');
        $comment->data = '{}';

        $activity = $this->getMock('Activity', array('processTags'));
        $activity->expects($this->never())->method('processTags');

        SugarTestReflection::callProtectedMethod($comment, 'processCommentTags', array($activity));
    }

    /**
     * @covers Comment::processCommentTags
     */
    public function testProcessCommentTags_TagsOnComment_ProcessTagsCalled()
    {
        $comment = BeanFactory::getBean('Comments');
        $comment->data = '{"tags":[{"module":"Foo","id":"123"}]}';

        $activity = $this->getMock('Activity', array('processTags'));
        $activity->expects($this->once())->method('processTags');

        SugarTestReflection::callProtectedMethod($comment, 'processCommentTags', array($activity));
    }
}
