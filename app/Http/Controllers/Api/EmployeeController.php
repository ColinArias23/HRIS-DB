<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class EmployeeController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/employees",
     *     tags={"Employees"},
     *     summary="Get all employees",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string", enum={"Active", "Inactive"})
     *     ),
     *     @OA\Parameter(
     *         name="employee_type",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string", example="Teaching")
     *     ),
     *     @OA\Response(response=200, description="List of employees")
     * )
     */
    public function index(Request $request)
    {
        $query = Employee::with('user');

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('employee_type')) {
            $query->where('employee_type', $request->employee_type);
        }

        $employees = $query->orderBy('created_at', 'desc')->get();
        return response()->json($employees);
    }

    /**
     * @OA\Post(
     *     path="/api/employees",
     *     tags={"Employees"},
     *     summary="Create new employee",
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"firstName", "lastName", "email", "position", "department"},
     *                 @OA\Property(property="user_id", type="integer"),
     *                 @OA\Property(property="firstName", type="string"),
     *                 @OA\Property(property="middleName", type="string"),
     *                 @OA\Property(property="lastName", type="string"),
     *                 @OA\Property(property="suffix", type="string"),
     *                 @OA\Property(property="email", type="string"),
     *                 @OA\Property(property="contact", type="string"),
     *                 @OA\Property(property="position", type="string"),
     *                 @OA\Property(property="department", type="string"),
     *                 @OA\Property(property="sex", type="string"),
     *                 @OA\Property(property="age", type="integer"),
     *                 @OA\Property(property="status", type="string", enum={"Active", "Inactive"}),
     *                 @OA\Property(property="employee_type", type="string", example="Teaching"),
     *                 @OA\Property(property="image", type="string", format="binary")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=201, description="Employee created successfully")
     * )
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'firstName' => 'required|string|max:255',
            'lastName' => 'required|string|max:255',
            'email' => 'required|email|unique:employees,email',
            'position' => 'required|string|max:255',
            'department' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'user_id' => 'nullable|exists:users,id',
            'employee_type' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $request->except('image');

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('avatars', 'public');
            $data['avatar'] = '/storage/' . $path;
        }

        $employee = Employee::create($data);

        return response()->json($employee->load('user'), 201);
    }

    /**
     * @OA\Get(
     *     path="/api/employees/{id}",
     *     tags={"Employees"},
     *     summary="Get employee by ID",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Employee details")
     * )
     */
    public function show($id)
    {
        $employee = Employee::with('user')->find($id);

        if (!$employee) {
            return response()->json(['message' => 'Employee not found'], 404);
        }

        return response()->json($employee);
    }

    /**
     * @OA\Post(
     *     path="/api/employees/{id}",
     *     tags={"Employees"},
     *     summary="Update employee (use POST with _method=PUT for file upload)",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Employee updated successfully")
     * )
     */
    public function update(Request $request, $id)
    {
        $employee = Employee::find($id);

        if (!$employee) {
            return response()->json(['message' => 'Employee not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'firstName' => 'sometimes|required|string|max:255',
            'lastName' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|unique:employees,email,' . $id,
            'position' => 'sometimes|required|string|max:255',
            'department' => 'sometimes|required|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $request->except(['image', '_method']);

        if ($request->hasFile('image')) {
            // Delete old avatar
            if ($employee->avatar) {
                $oldPath = str_replace('/storage/', '', $employee->avatar);
                Storage::disk('public')->delete($oldPath);
            }

            $path = $request->file('image')->store('avatars', 'public');
            $data['avatar'] = '/storage/' . $path;
        }

        $employee->update($data);

        return response()->json($employee->load('user'));
    }

    /**
     * @OA\Delete(
     *     path="/api/employees/{id}",
     *     tags={"Employees"},
     *     summary="Delete employee",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Employee deleted successfully")
     * )
     */
    public function destroy($id)
    {
        $employee = Employee::find($id);

        if (!$employee) {
            return response()->json(['message' => 'Employee not found'], 404);
        }

        // Delete avatar if exists
        if ($employee->avatar) {
            $path = str_replace('/storage/', '', $employee->avatar);
            Storage::disk('public')->delete($path);
        }

        $employee->delete();

        return response()->json(['message' => 'Employee deleted successfully']);
    }

    /**
     * @OA\Get(
     *     path="/api/employees/pending",
     *     tags={"Employees"},
     *     summary="Get pending employees",
     *     security={{"sanctum":{}}},
     *     @OA\Response(response=200, description="List of pending employees")
     * )
     */
    public function pendingEmployees()
    {
        $employees = Employee::inactive()->with('user')->orderBy('created_at', 'desc')->get();
        return response()->json($employees);
    }

    /**
     * @OA\Post(
     *     path="/api/employees/{id}/activate",
     *     tags={"Employees"},
     *     summary="Activate employee",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Employee activated successfully")
     * )
     */
    public function activateEmployee($id)
    {
        $employee = Employee::findOrFail($id);
        $employee->status = 'Active';
        $employee->save();

        return response()->json([
            'message' => 'Employee activated successfully',
            'employee' => $employee->load('user')
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/employees/{id}/deactivate",
     *     tags={"Employees"},
     *     summary="Deactivate employee",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Employee deactivated successfully")
     * )
     */
    public function deactivateEmployee($id)
    {
        $employee = Employee::findOrFail($id);
        $employee->status = 'Inactive';
        $employee->save();

        return response()->json([
            'message' => 'Employee deactivated successfully',
            'employee' => $employee->load('user')
        ]);
    }
}