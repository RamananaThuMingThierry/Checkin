export function getRoleCodes(user) {
  if (!Array.isArray(user?.roles)) {
    return [];
  }

  return user.roles
    .map((role) => role?.code)
    .filter(Boolean);
}

export function isSuperAdminUser(user) {
  return Boolean(user?.is_super_admin) || getRoleCodes(user).includes("platform-super-admin");
}

export function getDefaultRouteForUser(user) {
  if (isSuperAdminUser(user)) {
    return "/super-admin/dashboard";
  }

  return "/tenant-dashboard";
}
