<?php

namespace Helpers;


class GuideVideoHelper
{
    public function __construct()
    {
    }

    public function getGuideVideosWithTutorialSteps($guideVideos)
    {
        $tutorialSteps = array_values(config('tutorialSteps'));
        foreach ($guideVideos as $key => $guideVideo) {
            $tutorialStepData = array_values(array_filter($tutorialSteps, function ($tutorialStep) use ($guideVideo) {
                return $guideVideo['tutorial_step_tag'] == $tutorialStep['tutorial_step_tag'];
            }));
            $guideVideos[$key]['tutorial_step'] = isset($tutorialStepData[0])? $tutorialStepData[0] : null;
        }

        return $guideVideos;
    }
}
