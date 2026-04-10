import { createContext, useContext, useMemo, useState } from "react";

const I18nContext = createContext(null);

export function I18nProvider({ children }) {
  const [locale, setLocale] = useState("fr");

  const value = useMemo(
    () => ({
      locale,
      setLocale,
    }),
    [locale]
  );

  return <I18nContext.Provider value={value}>{children}</I18nContext.Provider>;
}

export function useI18n() {
  const context = useContext(I18nContext);

  if (!context) {
    throw new Error("useI18n must be used within an I18nProvider.");
  }

  return context;
}
