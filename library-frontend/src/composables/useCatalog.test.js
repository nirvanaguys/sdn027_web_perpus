import { beforeEach, describe, expect, it, vi } from "vitest";

vi.mock("../services/api", () => ({ default: { get: vi.fn() } }));
vi.mock("../stores/toast", () => ({ toastStore: { error: vi.fn() } }));

import api from "../services/api";
import { toastStore } from "../stores/toast";
import { useCatalog } from "./useCatalog";

const paginator = (books = []) => ({
  data: { data: books, current_page: 2, last_page: 5, total: 48 },
});

describe("catalog module", () => {
  beforeEach(() => vi.clearAllMocks());

  it("sends page, page size, and drops empty filters", async () => {
    api.get.mockResolvedValue({ data: paginator([{ id: 1 }]) });

    await useCatalog(15).load({ search: "fisika", format: "", grade_level: null }, 2);

    expect(api.get).toHaveBeenCalledWith("/books", {
      params: { per_page: 15, page: 2, search: "fisika" },
    });
  });

  it("unwraps the paginator and the server summary", async () => {
    const meta = {
      counts: { total: 48, pdf: 30, epub: 18, ready: 40 },
      categories: ["Sains", "Umum"],
    };
    api.get.mockResolvedValue({ data: { ...paginator([{ id: 7 }]), meta } });

    const { books, pagination, counts, categories, loading, load } = useCatalog(12);
    expect(loading.value).toBe(false);
    await load({}, 2);

    expect(books.value).toEqual([{ id: 7 }]);
    expect(pagination.value).toEqual({ current_page: 2, last_page: 5, total: 48 });
    expect(counts.value).toEqual(meta.counts);
    expect(categories.value).toEqual(meta.categories);
    expect(loading.value).toBe(false);
  });

  it("keeps the previous state and reports a failed load", async () => {
    api.get.mockRejectedValue(new Error("offline"));

    const { books, pagination, counts, loading, load } = useCatalog(12);
    await load({}, 1);

    expect(books.value).toEqual([]);
    expect(pagination.value.total).toBe(0);
    expect(counts.value.ready).toBe(0);
    expect(loading.value).toBe(false);
    expect(toastStore.error).toHaveBeenCalledOnce();
  });
});
