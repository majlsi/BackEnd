<?php

namespace App\Http\Controllers;

use Helpers\SecurityHelper;
use Illuminate\Http\Request;
use Models\FaqSection;
use Services\FaqSectionService;
use Validator;

class FaqSectionController extends Controller
{

    private $faqSectionService;
    private $securityHelper;

    public function __construct(FaqSectionService $faqSectionService, SecurityHelper $securityHelper)
    {
        $this->faqSectionService = $faqSectionService;
        $this->securityHelper = $securityHelper;
    }

    public function index()
    {
        return response()->json($this->faqSectionService->getAll(), 200);
    }

    public function show($id)
    {
        $section = $this->faqSectionService->getById($id);
        $section = $this->faqSectionService->setHasChilds($section);

        return response()->json($section, 200);
    }

    public function store(Request $request)
    {
        $data = $request->all();
        $user = $this->securityHelper->getCurrentUser();

        $validator = Validator::make($data, FaqSection::rules('save'));
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->all()], 400);
        }
        return response()->json($this->faqSectionService->create($data), 200);
    }

    public function update(Request $request, $id)
    {
        $data = $request->all();
        $user = $this->securityHelper->getCurrentUser();
        $faqSection = $this->faqSectionService->getById($id);
        $section = $this->faqSectionService->setHasChilds($faqSection);
        $errors = [];
        if ($section->hasChilds) {
            if (($section->parent_section_id != $data['parent_section_id']) && (($section->parent_section_id == null) || ($data['parent_section_id'] == null))) {
                $errors[0][] = ["error" => 'Can\'t update section parent ,it has child records',
                    "error_ar" => 'لا يمكن تعديل القسم الرئيسى لهذا القسم , لوجود بيانات مرتبطة بالقسم'];
            }
        }

        $validator = Validator::make($data, FaqSection::rules('update'), FaqSection::messages('update'));
        if ($validator->fails()) {
            $errors = array_merge($errors, array_values($validator->errors()->toArray()));
        }

        if (count($errors) > 0) {
            return response()->json(["error" => $errors], 400);
        }
        $this->faqSectionService->update($id, $data);
        return response()->json(['message' => 'FaqSection updated successfully'], 200);
    }

    public function destroy($id)
    {
        try {
            $user = $this->securityHelper->getCurrentUser();
            $this->faqSectionService->delete($id);
            return response()->json(['message' => "success"], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => "Can't delete this section!",
                'error_ar' => "لا يمكن حذف هذا القسم"], 400);
        }
    }

    public function getPagedList(Request $request)
    {
        $filter = (object) ($request->all());
        $user = $this->securityHelper->getCurrentUser();
        return response()->json($this->faqSectionService->getPagedList($filter), 200);
    }

    public function getParentSections(Request $request)
    {
        return response()->json($this->faqSectionService->getParentSections(), 200);
    }

    public function getLeafSections(Request $request)
    {
        return response()->json($this->faqSectionService->getLeafSections(), 200);
    }

    

}
