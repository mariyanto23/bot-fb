<?php

namespace App\controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Session;
use App\helpers\ValidatorHelper;
use App\models\TargetGroup;

final class TargetController extends Controller
{
    public function index(): void
    {
        $this->view('targets/index', [
            'title' => 'Target Groups',
            'targets' => (new TargetGroup())->all(),
        ]);
    }

    public function store(Request $request): void
    {
        $name = ValidatorHelper::sanitizeString($request->input('name', ''));
        $sourceUrl = trim((string) $request->input('source_url', ''));
        $facebookGroupId = ValidatorHelper::sanitizeString($request->input('facebook_group_id', ''));

        if ($name === '' || filter_var($sourceUrl, FILTER_VALIDATE_URL) === false) {
            Session::flash('error', 'Target name and a valid source URL are required.');
            redirect('/targets');
        }

        (new TargetGroup())->create($name, $sourceUrl, $facebookGroupId ?: null, (bool) $request->input('is_active', false));
        Session::flash('success', 'Target group created.');
        redirect('/targets');
    }

    public function update(Request $request): void
    {
        $id = (int) $request->input('id', 0);
        $name = ValidatorHelper::sanitizeString($request->input('name', ''));
        $sourceUrl = trim((string) $request->input('source_url', ''));
        $facebookGroupId = ValidatorHelper::sanitizeString($request->input('facebook_group_id', ''));

        if ($id < 1 || $name === '' || filter_var($sourceUrl, FILTER_VALIDATE_URL) === false) {
            Session::flash('error', 'Valid target group data is required.');
            redirect('/targets');
        }

        (new TargetGroup())->update($id, $name, $sourceUrl, $facebookGroupId ?: null, (bool) $request->input('is_active', false));
        Session::flash('success', 'Target group updated.');
        redirect('/targets');
    }

    public function delete(Request $request): void
    {
        $id = (int) $request->input('id', 0);
        if ($id > 0) {
            (new TargetGroup())->delete($id);
            Session::flash('success', 'Target group deleted.');
        }
        redirect('/targets');
    }
}
