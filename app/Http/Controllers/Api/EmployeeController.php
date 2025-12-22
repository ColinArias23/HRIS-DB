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
     * Get all employees
     * Frontend expects: array of employees
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
        
        // Return plain array (not wrapped in object)
        return response()->json($employees);
    }

    /**
     * Create new employee
     * Frontend sends FormData with image file
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

        // Handle image upload
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('avatars', 'public');
            $data['avatar'] = '/storage/' . $path;
        }

        $employee = Employee::create($data);

        return response()->json($employee->load('user'), 201);
    }

    /**
     * Get single employee
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
     * Update employee
     * Frontend sends FormData with _method=PUT
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

        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old avatar
            if ($employee->avatar) {
                $oldPath = str_replace('/storage/', '', parse_url($employee->avatar, PHP_URL_PATH));
                Storage::disk('public')->delete($oldPath);
            }

            $path = $request->file('image')->store('avatars', 'public');
            $data['avatar'] = '/storage/' . $path;
        }

        $employee->update($data);

        return response()->json($employee->load('user'));
    }

    /**
     * Delete employee
     */
    public function destroy($id)
    {
        $employee = Employee::find($id);

        if (!$employee) {
            return response()->json(['message' => 'Employee not found'], 404);
        }

        // Delete avatar if exists
        if ($employee->avatar) {
            $path = str_replace('/storage/', '', parse_url($employee->avatar, PHP_URL_PATH));
            Storage::disk('public')->delete($path);
        }

        $employee->delete();

        return response()->json(['message' => 'Employee deleted successfully']);
    }

    /**
     * Get pending employees
     */
    public function pendingEmployees()
    {
        $employees = Employee::inactive()->with('user')->orderBy('created_at', 'desc')->get();
        return response()->json($employees);
    }

    /**
     * Activate employee
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
     * Deactivate employee
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