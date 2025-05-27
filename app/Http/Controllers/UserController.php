    public function getStudentPurchases($user_id)
    {
        try {
            $user = \App\Models\User::find($user_id);

            if (!$user) {
                return ApiResponse::clientError('User not found', null, 404);
            }

            $purchases = \App\Models\Purchase::where('user_id', $user_id)
                ->with(['course', 'subject'])
                ->get();

            if ($purchases->isEmpty()) {
                return ApiResponse::clientError('No purchases found for this user', null, 404);
            }

            return ApiResponse::success('Purchases retrieved successfully', $purchases);
        } catch (\Throwable $th) {
            return ApiResponse::serverError('Failed to retrieve purchases: ' . $th->getMessage(), null, 500);
        }
    }