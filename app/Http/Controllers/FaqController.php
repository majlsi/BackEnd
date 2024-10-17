<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Services\FaqService;
use Models\Faq;
use Helpers\SecurityHelper;
use Validator;

class FaqController extends Controller {

    private $faqService;
    private $securityHelper;

    public function __construct(FaqService $faqService, SecurityHelper $securityHelper) {
        $this->faqService = $faqService;
        $this->securityHelper = $securityHelper;
    }

    public function index(){
        return response()->json($this->faqService->getAll(), 200);
    }

    public function show($id){
        return response()->json($this->faqService->getById($id), 200);
    }

    public function store(Request $request) {
        $data = $request->all();
        $user = $this->securityHelper->getCurrentUser();

        $validator = Validator::make($data, Faq::rules('save'));
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->all()], 400);
        }
        return response()->json($this->faqService->create($data), 200);
    }

    public function update(Request $request, $id) {
        $data = $request->all();
        $user = $this->securityHelper->getCurrentUser();
        $faq = $this->faqService->getById($id);
        
        $validator = Validator::make($data, Faq::rules('update'));
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->all()], 400);
        }
        $this->faqService->update($id,$data);
        return response()->json(['message' => 'Faq updated successfully'], 200);
    }  
    
    public function destroy($id) {
        try{
            $user = $this->securityHelper->getCurrentUser();
            $this->faqService->delete($id);
            return response()->json(['message' => "success"], 200);
        }catch (\Exception $e) {
            return response()->json(['error' => "Can't delete this faq!",
            'error_ar' => "لا يمكن حذف هذا السؤال"], 400);
        }
    }

    public function getPagedList(Request $request) {
        $filter = (object) ($request->all());
        $user = $this->securityHelper->getCurrentUser();
        return response()->json($this->faqService->getPagedList($filter),200);
    }

    public function getSectionQuestionsTree (){
        return response()->json($this->faqService->getSectionQuestionsTree(),200);

    }

    
}