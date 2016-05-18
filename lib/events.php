<?php
/**
 * All event handlers are bundled in this file
 */

/**
 * Make sure the last editor of a static page gets notified about a comment
 *
 * @param string         $event  'create'
 * @param string         $type   'object'
 * @param ElggAnnotation $comment the object that was just created
 *
 * @return void
 */
function static_create_comment_handler($event, $type, ElggAnnotation $comment) {
	
	// check of this is a comment
	if (empty($comment) || !($comment instanceof ElggAnnotation)) {
		return;
	}
	
	// only act on comments
	if ($comment->name !== "generic_comment") {
		return;
	}
	
	// is it a comment on a static page
	$entity = $comment->getEntity();
	if (empty($entity) || !elgg_instanceof($entity, "object", "static")) {
		return;
	}

	$comment_owner = $comment->getOwnerEntity();

	// get last revisor
	$revisions = $entity->getAnnotations("static_revision", 1, 0, "desc");

	$static_owner = $revisions[0]->getOwnerEntity();

	// @see actions/comment/save
	$subject = elgg_echo("generic_comment:email:subject");
	$message = elgg_echo("generic_comment:email:body", array(
		$entity->title,
		$comment_owner->name,
		$comment->value,
		$entity->getURL(),
		$comment_owner->name,
		$comment_owner->getURL()
	));

	// don't notify yourself
	if ($static_owner->getGUID() != $comment_owner->getGUID()) {
		notify_user($static_owner->getGUID(), $comment_owner->getGUID(), $subject, $message);
	}
}

/**
 * Listen to the delete event of an ElggObject to remove a static thumbnail when needed
 *
 * @param string     $event  'delete'
 * @param string     $type   'object'
 * @param ElggObject $entity the entity about to be removed
 *
 * @return void
 */
function static_delete_object_handler($event, $type, ElggObject $entity) {
	
	if (empty($entity) || !elgg_instanceof($entity, "object", "static")) {
		return;
	}
	
	if ($entity->icontime) {
		static_remove_thumbnail($entity->getGUID());
	}
}