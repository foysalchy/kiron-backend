<?php

namespace App\Services\Project;

use App\Models\ProjectTemplate;

class ProjectTemplateService
{
    public function getTemplates()
    {
        return ProjectTemplate::all();
    }

    public function getTemplateById(int $id)
    {
        return ProjectTemplate::findOrFail($id);
    }

    public function createTemplate(array $data)
    {
        return ProjectTemplate::create($data);
    }

    public function updateTemplate(int $id, array $data)
    {
        $template = ProjectTemplate::findOrFail($id);
        $template->update($data);
        return $template;
    }

    public function deleteTemplate(int $id)
    {
        $template = ProjectTemplate::findOrFail($id);
        return $template->delete();
    }
}
