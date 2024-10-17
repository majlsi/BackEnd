<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Models\CommitteeRecommendation;
use Illuminate\Support\Facades\Validator;
use Services\CommitteeRecommendationService;

class CommitteeRecommendationController extends Controller
{
    private CommitteeRecommendationService $committeeRecommendationService;

    public function __construct(
        CommitteeRecommendationService $committeeRecommendationService,
    ) {
        $this->committeeRecommendationService = $committeeRecommendationService;
    }

    public function update(Request $request, $id)
    {
        $data = $request->all([
            'recommendation_body', 'recommendation_date', 'responsible_user',
            'responsible_party', 'committee_final_output_id', 'recommendation_status_id'
        ]);
        $validator = Validator::make(
            $data,
            CommitteeRecommendation::rules('update'),
            CommitteeRecommendation::messages('update')
        );

        if ($validator->fails()) {
            return response()->json(["error" => array_values($validator->errors()->toArray())], 400);
        }

        $recommend = $this->committeeRecommendationService->update($id, $data);
        if ($recommend != null) {
            return response()->json([
                'Results' => $recommend->refresh(),
                'Messages' => [
                    'message' => 'The recommendation has been edited successfully',
                    'message_ar' => 'لقد تمت تعديل التوصية بنجاح'
                ]
            ], 200);
        }
    }

    public function destroy($id)
    {
        $this->committeeRecommendationService->delete($id);
        return response()->json([
            'Messages' => [
                'message' => 'The recommendation has been deleted successfully',
                'message_ar' => 'تم حذف التوصية بنجاح'
            ]
        ], 200);
    }
}
