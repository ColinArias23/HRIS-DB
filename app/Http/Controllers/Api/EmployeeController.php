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
     *     @OA\Response(
     *         response=200,
     *         description="List of employees",
     *         @OA\JsonContent(type="array", @OA\Items(type="object"))
     *     ),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function index()
    {
        $employees = Employee::orderBy('created_at', 'desc')->get();
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
     *                 @OA\Property(property="firstName", type="string", example="John"),
     *                 @OA\Property(property="middleName", type="string", example="Michael"),
     *                 @OA\Property(property="lastName", type="string", example="Doe"),
     *                 @OA\Property(property="suffix", type="string", example="Jr."),
     *                 @OA\Property(property="email", type="string", format="email", example="john.doe@example.com"),
     *                 @OA\Property(property="contact", type="string", example="+639171234567"),
     *                 @OA\Property(property="position", type="string", example="Software Engineer"),
     *                 @OA\Property(property="department", type="string", example="IT"),
     *                 @OA\Property(property="gender", type="string", enum={"Male", "Female"}, example="Male"),
     *                 @OA\Property(property="status", type="string", enum={"Active", "Inactive"}, example="Active"),
     *                 @OA\Property(property="address", type="string", example="123 Main St, Manila"),
     *                 @OA\Property(property="birthdate", type="string", format="date", example="1990-01-15"),
     *                 @OA\Property(property="salary", type="number", example=50000),
     *                 @OA\Property(property="employeeType", type="string", example="Full-time"),
     *                 @OA\Property(property="image", type="string", format="binary")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=201, description="Employee created successfully"),
     *     @OA\Response(response=422, description="Validation error")
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
            'gender' => 'nullable|in:Male,Female',
            'status' => 'nullable|in:Active,Inactive',
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

        return response()->json($employee, 201);
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
     *     @OA\Response(response=200, description="Employee details"),
     *     @OA\Response(response=404, description="Employee not found")
     * )
     */
    public function show($id)
    {
        $employee = Employee::find($id);

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
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(property="_method", type="string", example="PUT"),
     *                 @OA\Property(property="firstName", type="string"),
     *                 @OA\Property(property="lastName", type="string"),
     *                 @OA\Property(property="email", type="string"),
     *                 @OA\Property(property="image", type="string", format="binary")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=200, description="Employee updated successfully"),
     *     @OA\Response(response=404, description="Employee not found")
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
            'gender' => 'nullable|in:Male,Female',
            'status' => 'nullable|in:Active,Inactive',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $request->except(['image', '_method']);

        if ($request->hasFile('image')) {
            // Delete old avatar if exists
            if ($employee->avatar) {
                $oldPath = str_replace('/storage/', '', $employee->avatar);
                Storage::disk('public')->delete($oldPath);
            }

            $path = $request->file('image')->store('avatars', 'public');
            $data['avatar'] = '/storage/' . $path;
        }

        $employee->update($data);

        return response()->json($employee);
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
     *     @OA\Response(response=200, description="Employee deleted successfully"),
     *     @OA\Response(response=404, description="Employee not found")
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
}