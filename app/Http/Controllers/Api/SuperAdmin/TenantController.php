<?php

namespace App\Http\Controllers\Api\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\TenantRequest;
use App\Services\ActivityLogService;
use App\Services\TenantService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TenantController extends Controller
{
    public function __construct(
        private readonly TenantService $tenantService,
        private readonly ActivityLogService $activityLogService
    ) {}

    public function index()
    {
        try{
                    $constraints = [];

        $tenants = $this->tenantService->getAllTenants(
            keys: array_keys($constraints),
            value: array_values($constraints)
        );

        return response()->json([
            'data' => $tenants,
            'success' => true,
        ], 200);
        }catch (\Exception $e) {

            $this->activityLogService->createActivityLog([
                'user_id' => Auth::id(),
                'action' => 'index_tenants_failed',
                'entity_type' => 'tenant',
                'entity_id' => null,
                'message' => 'Erreur lors du chargement de la liste des entreprises.',
                'color' => 'danger',
                'method' => 'GET',
                'route' => 'api.superadmin.tenants.index',
                'status_code' => 500,
                'metadata' => [
                    'request_data' => $constraints,
                ],
            ]);


            return response()->json([
                'message' => 'Erreur lors du chargement de la liste des entreprises.',
                'error' => $e->getMessage(),
                'success' => false,
            ], 500);
        }
    }

    public function store(TenantRequest $request)
    {
        $data = $request->validated();

        try {
            DB::beginTransaction();

            $tenant = $this->tenantService->createTenant($data);

            $this->activityLogService->createActivityLog([
                'user_id' => Auth::id(),
                'action' => 'created',
                'entity_type' => 'tenant',
                'entity_id' => $tenant->id,
                'message' => 'Created tenant with ID: ' . $tenant->id,
                'color' => 'success',
                'method' => 'POST',
                'route' => 'api.superadmin.tenants.store',
                'status_code' => 201,
                'metadata' => [
                    'tenant_name' => $tenant->name,
                    'tenant_code' => $tenant->code,
                ],
            ]);

            DB::commit();

            return response()->json([
                'data' => $tenant,
                'message' => 'Enregistrement de l\'entreprise réussi',
                'success' => true,
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

            $this->activityLogService->createActivityLog([
                'user_id' => Auth::id(),
                'action' => 'failed_creation',
                'entity_type' => 'tenant',
                'entity_id' => null,
                'message' => 'Erreur lors de la création de l\'entreprise.',
                'color' => 'danger',
                'method' => 'POST',
                'route' => 'api.superadmin.tenants.store',
                'status_code' => 500,
                'metadata' => [
                    'request_data' => $data,
                ],
            ]);

            return response()->json([
                'message' => 'Erreur lors de la création de l\'entreprise.',
                'error' => $e->getMessage(),
                'success' => false,
            ], 500);
        }
    }

    public function show(string $encryptedId)
    {
        try{
            $id = decrypt_to_int_or_null($encryptedId);

            if(is_null($id)) {
                return response()->json([
                    'message' => 'ID d\'entreprise invalide',
                    'success' => false,
                ], 400);
            }

            $tenant = $this->tenantService->getTenantById($id);

            if (!$tenant) {
                return response()->json([
                    'message' => 'Entreprise non trouvée',
                    'success' => false,
                ], 404);
            }

            return response()->json([
                'data' => $tenant,
                'success' => true,
            ], 200);
        }catch (\Exception $e) {
            $this->activityLogService->createActivityLog([
                'user_id' => Auth::id(),
                'action' => 'show_tenant_failed',
                'entity_type' => 'tenant',
                'entity_id' => null,
                'message' => 'Erreur lors du chargement de l\'entreprise.',
                'color' => 'danger',
                'method' => 'GET',
                'route' => 'api.superadmin.tenants.show',
                'status_code' => 500,
                'metadata' => [
                    'request_data' => $encryptedId,
                ],
            ]);

            return response()->json([
                'message' => 'Erreur lors du chargement de l\'entreprise.',
                'error' => $e->getMessage(),
                'success' => false,
            ], 500);
        }
    }

    public function update(TenantRequest $request, string $encryptedId)
    {
        $data = $request->validated();

        try {
            DB::beginTransaction();

            $id = decrypt_to_int_or_null($encryptedId);

            if(is_null($id)) {
                return response()->json([
                    'message' => 'ID d\'entreprise invalide',
                    'success' => false,
                ], 400);
            }

            $tenant = $this->tenantService->updateTenant($id, $data);

            $this->activityLogService->createActivityLog([
                'user_id' => Auth::id(),
                'action' => 'updated',
                'entity_type' => 'tenant',
                'entity_id' => $tenant->id,
                'message' => 'Updated tenant with ID: ' . $tenant->id,
                'color' => 'primary',
                'method' => 'PUT',
                'route' => 'api.superadmin.tenants.update',
                'status_code' => 200,
                'metadata' => [
                    'tenant_name' => $tenant->name,
                    'tenant_code' => $tenant->code,
                ],
            ]);

            DB::commit();

            return response()->json([
                'data' => $tenant,
                'message' => 'Mise à jour de l\'entreprise réussie',
                'success' => true,
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();

            $this->activityLogService->createActivityLog([
                'user_id' => Auth::id(),
                'action' => 'failed_update',
                'entity_type' => 'tenant',
                'entity_id' => null,
                'message' => 'Erreur lors de la mise à jour de l\'entreprise.',
                'color' => 'danger',
                'method' => 'PUT',
                'route' => 'api.superadmin.tenants.update',
                'status_code' => 500,
                'metadata' => [
                    'request_data' => $data,
                ],
            ]);

            return response()->json([
                'message' => 'Erreur lors de la mise à jour de l\'entreprise.',
                'error' => $e->getMessage(),
                'success' => false,
            ], 500);
        }
    }

    public function destroy(string $encryptedId)
    {
        try {
            $id = decrypt_to_int_or_null($encryptedId);

            if(is_null($id)) {
                return response()->json([
                    'message' => 'ID d\'entreprise invalide',
                    'success' => false,
                ], 400);
            }

            $this->tenantService->deleteTenant($id);

            $this->activityLogService->createActivityLog([
                'user_id' => Auth::id(),
                'action' => 'deleted',
                'entity_type' => 'tenant',
                'entity_id' => $id,
                'message' => 'Deleted tenant with ID: ' . $id,
                'color' => 'danger',
                'method' => 'DELETE',
                'route' => 'api.superadmin.tenants.destroy',
                'status_code' => 200,
            ]);

            return response()->json([
                'message' => 'Entreprise supprimée avec succès',
                'success' => true,
            ], 200);

        } catch (\Exception $e) {
            $this->activityLogService->createActivityLog([
                'user_id' => Auth::id(),
                'action' => 'failed_deletion',
                'entity_type' => 'tenant',
                'entity_id' => null,
                'message' => 'Erreur lors de la suppression de l\'entreprise.',
                'color' => 'danger',
                'method' => 'DELETE',
                'route' => 'api.superadmin.tenants.destroy',
                'status_code' => 500,
                'metadata' => [
                ],
            ]);

            return response()->json([
                'message' => 'Erreur lors de la suppression de l\'entreprise.',
                'error' => $e->getMessage(),
                'success' => false,
            ], 500);
        }
    }

    public function restore(string $encryptedId)
    {
        try {
            $id = decrypt_to_int_or_null($encryptedId);

            if(is_null($id)) {
                return response()->json([
                    'message' => 'ID d\'entreprise invalide',
                    'success' => false,
                ], 400);
            }

            $tenant = $this->tenantService->restoreTenant($id);

            $this->activityLogService->createActivityLog([
                'user_id' => Auth::id(),
                'action' => 'restored',
                'entity_type' => 'tenant',
                'entity_id' => $tenant?->id,
                'message' => 'Restored tenant with ID: ' . $tenant?->id,
                'color' => 'success',
                'method' => 'POST',
                'route' => 'api.superadmin.tenants.restore',
                'status_code' => 200,
            ]);

            return response()->json([
                'data' => $tenant,
                'message' => 'Entreprise restaurée avec succès',
                'success' => true,
            ], 200);

        } catch (\Exception $e) {
            $this->activityLogService->createActivityLog([
                'user_id' => Auth::id(),
                'action' => 'failed_restoration',
                'entity_type' => 'tenant',
                'entity_id' => null,
                'message' => 'Erreur lors de la restauration de l\'entreprise.',
                'color' => 'danger',
                'method' => 'POST',
                'route' => 'api.superadmin.tenants.restore',
                'status_code' => 500,
                'metadata' => [
                    'request_data' => [
                        'id' => $encryptedId,
                    ],
                ],
            ]);

            return response()->json([
                'message' => 'Erreur lors de la restauration de l\'entreprise.',
                'error' => $e->getMessage(),
                'success' => false,
            ], 500);
        }
    }

    public function forceDelete(string $encryptedId)
    {
        try {
            $id = decrypt_to_int_or_null($encryptedId);

            if(is_null($id)) {
                return response()->json([
                    'message' => 'ID d\'entreprise invalide',
                    'success' => false,
                ], 400);
            }

            $this->tenantService->forceDeleteTenant($id);

            $this->activityLogService->createActivityLog([
                'user_id' => Auth::id(),
                'action' => 'force_deleted',
                'entity_type' => 'tenant',
                'entity_id' => $id,
                'message' => 'Force deleted tenant with ID: ' . $id,
                'color' => 'danger',
                'method' => 'DELETE',
                'route' => 'api.superadmin.tenants.force_delete',
                'status_code' => 200,
            ]);

            return response()->json([
                'message' => 'Entreprise supprimée définitivement avec succès',
                'success' => true,
            ], 200);

        } catch (\Exception $e) {
            $this->activityLogService->createActivityLog([
                'user_id' => Auth::id(),
                'action' => 'failed_force_deletion',
                'entity_type' => 'tenant',
                'entity_id' => null,
                'message' => 'Erreur lors de la suppression définitive de l\'entreprise.',
                'color' => 'danger',
                'method' => 'DELETE',
                'route' => 'api.superadmin.tenants.force_delete',
                'status_code' => 500,
                'metadata' => [
                    'request_data' => [
                        'id' => $encryptedId,
                    ],
                ],
            ]);

            return response()->json([
                'message' => 'Erreur lors de la suppression définitive de l\'entreprise.',
                'error' => $e->getMessage(),
                'success' => false,
            ], 500);
        }
    }
}

