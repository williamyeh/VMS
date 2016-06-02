<?php

namespace App\Http\Controllers\Api\V1_0;

use App\Http\Controllers\Api\BaseAuthController;
use App\Http\Requests\Api\V1_0\FillBulkCustomFieldsRequest;
use App\Http\Requests\Api\V1_0\CreateProjectCustomFieldRequest;
use App\Http\Requests\Api\V1_0\ShowAllMembersCustomFieldDataRequest;
use App\Http\Requests\Api\V1_0\ShowProjectRequest;
use App\Http\Requests\Api\V1_0\FillCustomFieldRequest;
use App\Repositories\ProjectCustomFieldRepository;
use App\Repositories\MemberCustomFieldDataRepository;
use App\Repositories\MemberCustomFieldDataDbRepository;
use App\Project;
use App\CustomField\TypeMapping;
use App\Services\TransformerService;
use Illuminate\Support\Arr;

class ProjectCustomFieldController extends BaseAuthController
{
    public function store(
        CreateProjectCustomFieldRequest $request,
        ProjectCustomFieldRepository $repository,
        $id
    ) {
        $project = Project::findOrFail($id);
        $data = $request->input('data.attributes');

        if ($request->has('data.id')) {
            $customField = $repository->update($project, $request->input('data.id'), $data);
        } else {
            $metadata = (isset($data['metadata'])) ? $data['metadata'] : null;
            $customField = $repository->newInstance(
                $data['name'],
                $data['description'],
                $data['required'],
                $data['type'],
                $data['order'],
                $metadata
            );

            $project->customFields()->save($customField);
        }

        // var_dump($customField);

        $manager = TransformerService::getJsonApiManager();
        $resource = TransformerService::getResourceItem(
            $customField,
            'App\Transformers\JsonApiProjectCustomFieldTransformer',
            'project_custom_fields'
        );

        $result = $manager->createData($resource)->toArray();
        $status = 201;

        return response()->json($result, $status);
    }

    public function showAll($projectId)
    {
        $project = Project::find($projectId);
        $customFields = $project->customFields()->get();

        $manager = TransformerService::getJsonApiManager();
        $resource = TransformerService::getResourceCollection(
            $customFields,
            'App\Transformers\JsonApiProjectCustomFieldTransformer',
            'project_custom_fields'
        );

        $result = $manager->createData($resource)->toArray();
        $status = 200;

        return response()->json($result, $status);
    }

    public function fillCustomField(FillCustomFieldRequest $request,
        MemberCustomFieldDataRepository $repository, $projectId)
    {
        $user = $this->jwtService->getUser();

        /**
         * TODO: Should be validated
         */
        $data = $request->input('data.attributes.content');
        $customFieldId = $request->input('data.relationships.custom_field.data.id');
        $project = Project::find($projectId);

        $repository->updateOrCreate($project, $user, $customFieldId, $data);

        $result = [];
        $status = 201;

        return response()->json($result, $status);
    }

    public function fillBulkCustomFields(FillBulkCustomFieldsRequest $request,
        MemberCustomFieldDataRepository $repository, $projectId)
    {
        $user = $this->jwtService->getUser();

        // TODO Should validate the project_custom_field.id exists
        $collection = collect($request->input('data'));
        $project = Project::find($projectId);
        $mapping = [];

        /**
         * @TODO MUST validate the custom field id is able to be updated
         */
        $collection->each(function ($customFieldData) use (&$mapping) {
            $customFieldId = Arr::get($customFieldData, 'relationships.project_custom_field.data.id');
            $mapping[$customFieldId] = Arr::get($customFieldData, 'attributes.data');
        });

        $repository->updateOrCreateMany($project, $user, $mapping);

        $result = [];
        $status = 201;

        return response()->json($result, $status);
    }

    public function showAllCutsomFieldsData(ShowProjectRequest $request,
        MemberCustomFieldDataRepository $repository, $projectId)
    {
        $user = $this->jwtService->getUser();
        $project = Project::find($projectId);

        $memberCustomFieldData = $repository->getVolunteerAllCustomFieldData($project, $user);

        if ($memberCustomFieldData === null) {
            $result = [];
            $status = 204;
        } else {
            $manager = TransformerService::getJsonApiManager();
            $resource = TransformerService::getResourceCollection(
                $memberCustomFieldData,
                'App\Transformers\JsonApiMemberCustomFieldsDataTransformer',
                'custom_field_data'
            );

            $manager->parseIncludes(['project_custom_field']);

            $result = $manager->createData($resource)->toArray();
            $status = 200;
        }

        return response()->json($result, $status);
    }

    public function showAllMembersCustomFieldData(
        ShowAllMembersCustomFieldDataRequest $request,
        MemberCustomFieldDataDbRepository $repository,
        $projectId
    ) {
        $project = Project::find($projectId);
        $customFieldSets = $repository->getAllProjectCustomFieldData($project);

        $manager = TransformerService::getJsonApiManager();
        $resource = TransformerService::getResourceCollection(
            $customFieldSets,
            'App\Transformers\JsonApiMemberCustomFieldsDbTransformer',
            'custom_field_data'
        );

        $manager->parseIncludes(['project_custom_field', 'member']);

        $result = $manager->createData($resource)->toArray();
        $status = 200;

        return response()->json($result, $status);
    }
}