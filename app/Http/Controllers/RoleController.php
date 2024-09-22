<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateRoleRequest;
use App\Http\Requests\UpdateRoleRequest;
use Illuminate\Http\Request;
use App\Services\DataStorageInterface;

class RoleController extends Controller
{
    protected $dataStorage;

    public function __construct(DataStorageInterface $dataStorage)
    {
        $this->dataStorage = $dataStorage;
    }

    public function index()
    {
        $roles = $this->dataStorage->getAllRoles();
        return response()->json($roles);
    }

    public function store(CreateRoleRequest $request)
    {
        $roleData = $request->validated();
        $roleId = $this->dataStorage->createRole($roleData);
        return response()->json(['id' => $roleId, 'role' => $roleData], 201);
    }

    public function update(UpdateRoleRequest $request, $id){
        $roleData = $request->validated();
        $this->dataStorage->updateRole($id, $roleData);
        return response()->json($roleData,);
    }

    public function destroy($id){
        $this->dataStorage->deleteRole($id);
        return response()->json(null, 204);
    }
}