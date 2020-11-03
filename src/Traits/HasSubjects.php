<?php

namespace RenokiCo\PhpK8s\Traits;

use RenokiCo\PhpK8s\Instances\Subject;
use RenokiCo\PhpK8s\K8s;

trait HasSubjects
{
    /**
     * Add a new subject.
     *
     * @param  array|\RenokiCo\PhpK8s\Instances\Subject  $subject
     * @return $this
     */
    public function addSubject($subject)
    {
        if ($subject instanceof Subject) {
            $subject = $subject->toArray();
        }

        return $this->addToAttribute('subjects', $subject);
    }

    /**
     * Batch-add multiple roles.
     *
     * @param  array  $subjects
     * @return $this
     */
    public function addSubjects(array $subjects)
    {
        foreach ($subjects as $subject) {
            $this->addSubject($subject);
        }

        return $this;
    }

    /**
     * Set the subjects for the resource.
     *
     * @param  array  $subjects
     * @return  $this
     */
    public function setSubjects(array $subjects)
    {
        foreach ($subjects as &$subject) {
            if ($subject instanceof Subject) {
                $subject = $subject->toArray();
            }
        }

        return $this->setAttribute('subjects', $subjects);
    }

    /**
     * Get the subjects from the resource.
     *
     * @param  bool  $asInstance
     * @return array
     */
    public function getSubjects(bool $asInstance = true): array
    {
        $subjects = $this->getAttribute('subjects', []);

        if ($asInstance) {
            foreach ($subjects as &$subject) {
                $subject = K8s::subject($subject);
            }
        }

        return $subjects;
    }
}
