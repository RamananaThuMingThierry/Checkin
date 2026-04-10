const WORKFLOW_STORAGE_KEY = "pointages.superadmin.workflow";

const defaultWorkflow = {
  tenant: null,
  offer: null,
  subscription: null,
  invoice: null,
};

function readWorkflow() {
  if (typeof window === "undefined") {
    return defaultWorkflow;
  }

  try {
    const raw = window.localStorage.getItem(WORKFLOW_STORAGE_KEY);

    if (!raw) {
      return defaultWorkflow;
    }

    return {
      ...defaultWorkflow,
      ...JSON.parse(raw),
    };
  } catch {
    return defaultWorkflow;
  }
}

function writeWorkflow(nextValue) {
  if (typeof window === "undefined") {
    return;
  }

  window.localStorage.setItem(WORKFLOW_STORAGE_KEY, JSON.stringify(nextValue));
}

export function getWorkflowSnapshot() {
  return readWorkflow();
}

export function saveWorkflowEntry(key, value) {
  const snapshot = readWorkflow();
  const nextValue = {
    ...snapshot,
    [key]: value,
  };

  writeWorkflow(nextValue);
  return nextValue;
}
